<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class GenerateEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {name?} {--namespace= : Custom namespace to override entity type namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate entity files (Model, Migration, Request, Resource, Seeder, Service, Filter)';

    /**
     * Entity name.
     */
    protected string $entityName;

    /**
     * Selected namespace key from config.
     */
    protected string $namespaceKey;

    /**
     * Custom namespace (optional).
     */
    protected ?string $customNamespace;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->entityName = $this->argument('name') ?? text(
            label: 'What is the entity name?',
            placeholder: 'e.g., Product, Category, Order',
            required: true
        );

        $this->entityName = Str::studly($this->entityName);

        $entities = config('entities', []);
        $namespaceOptions = array_filter(array_keys($entities), fn($key) => $key !== 'api_version');

        if (empty($namespaceOptions)) {
            $this->error('No namespaces configured in config/entities.php');
            return self::FAILURE;
        }

        $this->namespaceKey = select(
            label: 'Select namespace',
            options: $namespaceOptions,
            default: $namespaceOptions[0] ?? 'user'
        );

        $this->customNamespace = $this->option('namespace');

        if ($this->customNamespace) {
            $namespace = Str::studly($this->customNamespace);
            $this->info("Using custom namespace: {$namespace}");
        } else {
            $namespace = $entities[$this->namespaceKey]['namespace'] ?? Str::studly($this->namespaceKey);
        }

        $generateAll = confirm(
            label: 'Generate all files?',
            default: true,
            hint: 'Choose "No" to select specific files'
        );

        if ($generateAll) {
            $selected = [
                'model',
                'migration',
                'request',
                'resource',
                'seeder',
                'service',
                'filter',
                'repository',
                'controller',
            ];
        } else {
            $options = [
                'model'      => 'Model',
                'migration'  => 'Migration',
                'request'    => 'Request (Store & Update)',
                'resource'   => 'Resource',
                'seeder'     => 'Seeder',
                'service'    => 'Service',
                'filter'     => 'Query Filter',
                'repository' => 'Repository & Contract',
                'controller' => 'Controller',
            ];

            $this->newLine();
            $this->info('Answer yes/no for each file to generate:');
            $this->newLine();

            $selected = [];
            foreach ($options as $key => $label) {
                if (confirm(label: "Include {$label}?", default: true)) {
                    $selected[] = $key;
                }
            }

            if (empty($selected)) {
                $this->error('No files selected. Aborting.');
                return self::FAILURE;
            }
        }

        $generated = [];

        foreach ($selected as $type) {
            $method = 'generate' . Str::studly($type);
            if (method_exists($this, $method)) {
                $result = $this->$method($namespace);
                if ($result) {
                    $generated[] = $type;
                }
            }
        }

        if (!empty($generated)) {
            $this->newLine();
            $this->info('✓ Successfully generated: ' . implode(', ', $generated));
            $this->newLine();
            $this->comment('Next steps:');
            $this->line('  1. Update the migration file with your table schema');
            $this->line('  2. Implement validation rules in Request classes');
            $this->line('  3. Customize Resource transformation');
            $this->line('  4. Add routes to routes/' . ($entities[$this->namespaceKey]['routes'] ?? 'api.php'));
            $this->line('  5. Register repository binding in RepositoryServiceProvider');

            if ($this->customNamespace) {
                $this->newLine();
                $this->comment("Note: Files generated with custom namespace '{$namespace}'");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Generate Model.
     */
    protected function generateModel(string $namespace): bool
    {
        $path = app_path("Models/{$this->entityName}.php");

        if (File::exists($path)) {
            if (!confirm("Model {$this->entityName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Model");
                return false;
            }
        }

        $stub = $this->getStub('model');
        $content = $this->replaceStubVariables($stub, [
            'EntityName' => $this->entityName,
            'tableName' => Str::snake(Str::plural($this->entityName)),
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✓ Model created: {$path}");
        return true;
    }

    /**
     * Generate Migration.
     */
    protected function generateMigration(string $namespace): bool
    {
        $tableName = Str::snake(Str::plural($this->entityName));
        $className = 'Create' . Str::plural($this->entityName) . 'Table';
        $fileName = date('Y_m_d_His') . '_create_' . $tableName . '_table.php';
        $path = database_path("migrations/{$fileName}");

        $stub = $this->getStub('migration');
        $content = $this->replaceStubVariables($stub, [
            'ClassName' => $className,
            'tableName' => $tableName,
        ]);

        File::put($path, $content);

        $this->info("✓ Migration created: {$path}");
        return true;
    }

    /**
     * Generate Request files.
     */
    protected function generateRequest(string $namespace): bool
    {
        $basePath = app_path("Http/Requests/{$namespace}");
        File::ensureDirectoryExists($basePath);

        $requests = ['Store', 'Update'];
        $created = [];

        foreach ($requests as $type) {
            $requestName = "{$type}{$this->entityName}Request";
            $path = "{$basePath}/{$requestName}.php";

            if (File::exists($path)) {
                if (!confirm("{$requestName} already exists. Overwrite?", false)) {
                    continue;
                }
            }

            $stub = $this->getStub('request');
            $content = $this->replaceStubVariables($stub, [
                'Namespace' => $namespace,
                'EntityName' => $this->entityName,
                'RequestType' => $type,
            ]);

            File::put($path, $content);
            $created[] = $requestName;
        }

        if (!empty($created)) {
            $this->info("✓ Requests created: " . implode(', ', $created));
            return true;
        }

        $this->warn("Skipped: Requests");
        return false;
    }

    /**
     * Generate Resource.
     */
    protected function generateResource(string $namespace): bool
    {
        $resourceName = "{$this->entityName}Resource";
        $path = app_path("Http/Resources/{$namespace}/{$resourceName}.php");

        if (File::exists($path)) {
            if (!confirm("{$resourceName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Resource");
                return false;
            }
        }

        $stub = $this->getStub('resource');
        $content = $this->replaceStubVariables($stub, [
            'Namespace' => $namespace,
            'EntityName' => $this->entityName,
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✓ Resource created: {$path}");
        return true;
    }

    /**
     * Generate Seeder.
     */
    protected function generateSeeder(string $namespace): bool
    {
        $seederName = "{$this->entityName}Seeder";
        $path = database_path("seeders/{$seederName}.php");

        if (File::exists($path)) {
            if (!confirm("{$seederName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Seeder");
                return false;
            }
        }

        $stub = $this->getStub('seeder');
        $content = $this->replaceStubVariables($stub, [
            'EntityName' => $this->entityName,
        ]);

        File::put($path, $content);

        $this->info("✓ Seeder created: {$path}");
        return true;
    }

    /**
     * Generate Service.
     */
    protected function generateService(string $namespace): bool
    {
        $serviceName = "{$this->entityName}Service";
        $path = app_path("Services/{$serviceName}.php");

        if (File::exists($path)) {
            if (!confirm("{$serviceName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Service");
                return false;
            }
        }

        $stub = $this->getStub('service');
        $content = $this->replaceStubVariables($stub, [
            'EntityName' => $this->entityName,
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✓ Service created: {$path}");
        return true;
    }

    /**
     * Generate Query Filter.
     */
    protected function generateFilter(string $namespace): bool
    {
        $filterName = "{$this->entityName}Filter";
        $path = app_path("Http/Filters/{$filterName}.php");

        if (File::exists($path)) {
            if (!confirm("{$filterName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Filter");
                return false;
            }
        }

        $stub = $this->getStub('filter');
        $content = $this->replaceStubVariables($stub, [
            'EntityName' => $this->entityName,
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✓ Filter created: {$path}");
        return true;
    }

    /**
     * Generate Repository and Contract.
     */
    protected function generateRepository(string $namespace): bool
    {
        $repositoryName = "{$this->entityName}Repository";
        $contractName = "{$this->entityName}RepositoryContract";

        $repoPath = app_path("Repositories/{$repositoryName}.php");
        $contractPath = app_path("Repositories/Contracts/{$contractName}.php");

        $created = [];

        if (File::exists($contractPath)) {
            if (!confirm("{$contractName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Repository Contract");
            } else {
                $stub = $this->getStub('repository-contract');
                $content = $this->replaceStubVariables($stub, [
                    'EntityName' => $this->entityName,
                ]);

                File::ensureDirectoryExists(dirname($contractPath));
                File::put($contractPath, $content);
                $created[] = 'Contract';
            }
        } else {
            $stub = $this->getStub('repository-contract');
            $content = $this->replaceStubVariables($stub, [
                'EntityName' => $this->entityName,
            ]);

            File::ensureDirectoryExists(dirname($contractPath));
            File::put($contractPath, $content);
            $created[] = 'Contract';
        }

        if (File::exists($repoPath)) {
            if (!confirm("{$repositoryName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Repository");
            } else {
                $stub = $this->getStub('repository');
                $content = $this->replaceStubVariables($stub, [
                    'EntityName' => $this->entityName,
                ]);

                File::ensureDirectoryExists(dirname($repoPath));
                File::put($repoPath, $content);
                $created[] = 'Repository';
            }
        } else {
            $stub = $this->getStub('repository');
            $content = $this->replaceStubVariables($stub, [
                'EntityName' => $this->entityName,
            ]);

            File::ensureDirectoryExists(dirname($repoPath));
            File::put($repoPath, $content);
            $created[] = 'Repository';
        }

        if (!empty($created)) {
            $this->info("✓ Repository files created: " . implode(', ', $created));
            return true;
        }

        return false;
    }

    /**
     * Generate Controller.
     */
    protected function generateController(string $namespace): bool
    {
        $controllerName = "{$this->entityName}Controller";
        $path = app_path("Http/Controllers/Api/{$namespace}/{$controllerName}.php");

        if (File::exists($path)) {
            if (!confirm("{$controllerName} already exists. Overwrite?", false)) {
                $this->warn("Skipped: Controller");
                return false;
            }
        }

        $stub = $this->getStub('controller');
        $content = $this->replaceStubVariables($stub, [
            'Namespace' => $namespace,
            'EntityName' => $this->entityName,
            'entityVariable' => Str::camel($this->entityName),
        ]);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->info("✓ Controller created: {$path}");
        return true;
    }

    /**
     * Get stub content.
     */
    protected function getStub(string $type): string
    {
        $stubPath = base_path("stubs/{$type}.stub");

        if (File::exists($stubPath)) {
            return File::get($stubPath);
        }

        return $this->getDefaultStub($type);
    }

    /**
     * Get default stub content.
     */
    protected function getDefaultStub(string $type): string
    {
        return match ($type) {
            'model' => $this->getModelStub(),
            'migration' => $this->getMigrationStub(),
            'request' => $this->getRequestStub(),
            'resource' => $this->getResourceStub(),
            'seeder' => $this->getSeederStub(),
            'service' => $this->getServiceStub(),
            'filter' => $this->getFilterStub(),
            'repository' => $this->getRepositoryStub(),
            'repository-contract' => $this->getRepositoryContractStub(),
            'controller' => $this->getControllerStub(),
            default => '',
        };
    }

    /**
     * Replace stub variables.
     */
    protected function replaceStubVariables(string $stub, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $stub = str_replace("{{ {$key} }}", $value, $stub);
            $stub = str_replace("{{{$key}}}", $value, $stub);
        }

        return $stub;
    }

    /**
     * Get model stub.
     */
    protected function getModelStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {{ EntityName }} extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '{{ tableName }}';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Add hidden attributes here
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Add casts here
    ];
}
STUB;
    }

    /**
     * Get migration stub.
     */
    protected function getMigrationStub(): string
    {
        return <<<'STUB'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{{ tableName }}', function (Blueprint $table) {
            $table->id();
            // Add your columns here
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{{ tableName }}');
    }
};
STUB;
    }

    /**
     * Get request stub.
     */
    protected function getRequestStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Requests\{{ Namespace }};

use Illuminate\Foundation\Http\FormRequest;

class {{ RequestType }}{{ EntityName }}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Add your validation rules here
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Add custom messages here
        ];
    }
}
STUB;
    }

    /**
     * Get resource stub.
     */
    protected function getResourceStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Resources\{{ Namespace }};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {{ EntityName }}Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Add your resource fields here
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
STUB;
    }

    /**
     * Get seeder stub.
     */
    protected function getSeederStub(): string
    {
        return <<<'STUB'
<?php

namespace Database\Seeders;

use App\Models\{{ EntityName }};
use Illuminate\Database\Seeder;

class {{ EntityName }}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // {{ EntityName }}::factory(10)->create();
        
        // Or create specific records
        // {{ EntityName }}::create([
        //     // Add your data here
        // ]);
    }
}
STUB;
    }

    /**
     * Get service stub.
     */
    protected function getServiceStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Services;

use App\Repositories\Contracts\{{ EntityName }}RepositoryContract;

class {{ EntityName }}Service extends BaseModelService
{
    /**
     * Create a new service instance.
     *
     * @param {{ EntityName }}RepositoryContract $repository
     */
    public function __construct({{ EntityName }}RepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Prepare data for create operation.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForCreate(array $data): array
    {
        // Handle file uploads using UploadService facade
        // Example: $data['image'] = UploadService::upload($data['image'], 'entity-folder');
        
        return $data;
    }

    /**
     * Prepare data for update operation.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForUpdate(array $data): array
    {
        // Handle file uploads using UploadService facade
        // Example: if (isset($data['image'])) {
        //     $data['image'] = UploadService::upload($data['image'], 'entity-folder');
        // }
        
        return $data;
    }
}
STUB;
    }

    /**
     * Get filter stub.
     */
    protected function getFilterStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Filters;

class {{ EntityName }}Filter extends BaseFilters
{
    /**
     * Registered filters to operate upon.
     *
     * @var array
     */
    protected $filters = [
        'search',
        'status',
        // Add more filters here
    ];

    /**
     * Filter by search term.
     *
     * @param string $value
     * @return void
     */
    protected function search($value)
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%");
            // Add more searchable fields here
        });
    }

    /**
     * Filter by status.
     *
     * @param string $value
     * @return void
     */
    protected function status($value)
    {
        $this->builder->where('status', $value);
    }
}
STUB;
    }

    /**
     * Get repository stub.
     */
    protected function getRepositoryStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Repositories;

use App\Http\Filters\{{ EntityName }}Filter;
use App\Models\{{ EntityName }};
use App\Repositories\Contracts\{{ EntityName }}RepositoryContract;
use Illuminate\Database\Eloquent\Model;

class {{ EntityName }}Repository extends BaseRepository implements {{ EntityName }}RepositoryContract
{
    /**
     * Resolve the model instance.
     *
     * @return Model
     */
    protected function resolveModel(): Model
    {
        return new {{ EntityName }}();
    }

    /**
     * Resolve the filter instance.
     *
     * @return {{ EntityName }}Filter|null
     */
    protected function resolveFilter(): ?{{ EntityName }}Filter
    {
        return new {{ EntityName }}Filter(request());
    }
}
STUB;
    }

    /**
     * Get repository contract stub.
     */
    protected function getRepositoryContractStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Repositories\Contracts;

interface {{ EntityName }}RepositoryContract extends RepositoryContract
{
    // Add custom repository methods here
}
STUB;
    }

    /**
     * Get controller stub.
     */
    protected function getControllerStub(): string
    {
        return <<<'STUB'
<?php

namespace App\Http\Controllers\Api\{{ Namespace }};

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\{{ Namespace }}\Store{{ EntityName }}Request;
use App\Http\Requests\{{ Namespace }}\Update{{ EntityName }}Request;
use App\Http\Resources\{{ Namespace }}\{{ EntityName }}Resource;
use App\Services\{{ EntityName }}Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {{ EntityName }}Controller extends BaseApiController
{
    /**
     * The service instance.
     *
     * @var {{ EntityName }}Service
     */
    protected $service;

    /**
     * Create a new controller instance.
     *
     * @param {{ EntityName }}Service $service
     */
    public function __construct({{ EntityName }}Service $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $options = [
            'perPage' => $request->boolean('paginate', true) ? $request->input('per_page', 15) : null,
            'withTrashed' => $request->boolean('with_trashed', false),
            'relations' => $request->input('relations', []),
            'applyFilters' => true,
            'orderBy' => $request->input('order_by', 'created_at'),
            'orderDirection' => $request->input('order_direction', 'desc'),
        ];

        $data = $this->service->get($options);

        return $this->successResponse(
            {{ EntityName }}Resource::collection($data),
            '{{ EntityName }} list retrieved successfully'
        );
    }

    /**
     * Store a newly created resource.
     *
     * @param Store{{ EntityName }}Request $request
     * @return JsonResponse
     */
    public function store(Store{{ EntityName }}Request $request): JsonResponse
    {
        ${{ entityVariable }} = $this->service->create($request->validated());

        return $this->createdResponse(
            new {{ EntityName }}Resource(${{ entityVariable }}),
            '{{ EntityName }} created successfully'
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $options = [
            'withTrashed' => $request->boolean('with_trashed', false),
            'relations' => $request->input('relations', []),
        ];

        ${{ entityVariable }} = $this->service->showOrFail($id, $options);

        return $this->successResponse(
            new {{ EntityName }}Resource(${{ entityVariable }}),
            '{{ EntityName }} retrieved successfully'
        );
    }

    /**
     * Update the specified resource.
     *
     * @param Update{{ EntityName }}Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Update{{ EntityName }}Request $request, int $id): JsonResponse
    {
        ${{ entityVariable }} = $this->service->update($id, $request->validated());

        if (!${{ entityVariable }}) {
            return $this->notFoundResponse('{{ EntityName }} not found');
        }

        return $this->successResponse(
            new {{ EntityName }}Resource(${{ entityVariable }}),
            '{{ EntityName }} updated successfully'
        );
    }

    /**
     * Remove the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $forceDelete = $request->boolean('force_delete', false);
        $deleted = $this->service->delete($id, $forceDelete);

        if (!$deleted) {
            return $this->notFoundResponse('{{ EntityName }} not found');
        }

        return $this->successResponse(null, '{{ EntityName }} deleted successfully');
    }

    /**
     * Restore the specified soft-deleted resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $restored = $this->service->restore($id);

        if (!$restored) {
            return $this->notFoundResponse('{{ EntityName }} not found or not soft-deleted');
        }

        return $this->successResponse(null, '{{ EntityName }} restored successfully');
    }

    /**
     * Delete multiple resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $forceDelete = $request->boolean('force_delete', false);
        $count = $this->service->deleteMultiple($request->input('ids'), $forceDelete);

        return $this->successResponse(
            ['count' => $count],
            "{$count} {{ EntityName }} deleted successfully"
        );
    }
}
STUB;
    }
}
