<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => 'يجب قبول :attribute',
    'active_url' => ':attribute لا يُمثّل رابطًا صحيحًا',
    'after' => 'يجب على :attribute أن يكون تاريخًا لاحقًا للتاريخ :date.',
    'after_or_equal' => ':attribute يجب أن يكون تاريخاً لاحقاً أو مطابقاً للتاريخ :date.',
    'alpha' => 'يجب أن لا يحتوي :attribute سوى على حروف',
    'alpha_dash' => 'يجب أن لا يحتوي :attribute سوى على حروف، أرقام ومطّات.',
    'alpha_num' => 'يجب أن يحتوي :attribute على حروفٍ وأرقامٍ فقط',
    'array' => 'يجب أن يكون :attribute ًمصفوفة',
    'before' => 'يجب على :attribute أن يكون تاريخًا سابقًا للتاريخ :date.',
    'before_or_equal' => ':attribute يجب أن يكون تاريخا سابقا أو مطابقا للتاريخ :date',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون طول :attribute بين :min و :max حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute إما true أو false ',
    'confirmed' => 'حقل التأكيد غير مُطابق للحقل :attribute',
    'date' => ':attribute ليس تاريخًا صحيحًا',
    'date_format' => 'لا يتوافق :attribute مع الشكل :format.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مُختلفان',
    'digits' => 'يجب أن يحتوي :attribute على :digits رقمًا/أرقام',
    'digits_between' => 'يجب أن يحتوي :attribute بين :min و :max رقمًا/أرقام ',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'للحقل :attribute قيمة مُكرّرة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح البُنية',
    'exists' => 'القيمة المحددة :attribute غير موجودة',
    'file' => 'الـ :attribute يجب أن يكون ملفا.',
    'filled' => ':attribute إجباري',
    'gt' => [
        'numeric' => 'لا يمكن أن تكون قيمة :attribute صفراً أو سالبة.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت',
        'string' => 'يجب أن يكون طول النّص :attribute أكثر من :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر/عنصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :value كيلوبايت',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :value عُنصرًا/عناصر',
    ],
    'image' => 'يجب أن يكون :attribute صورةً',
    'in' => ':attribute غير موجود',
    'in_array' => ':attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيحًا',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيحًا.',
    'json' => 'يجب أن يكون :attribute نصآ من نوع JSON.',
    'lt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute أصغر من :value كيلوبايت',
        'string' => 'يجب أن يكون طول النّص :attribute أقل من :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر/عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'max' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'mimes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'mimetypes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'min' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :min حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عُنصرًا/عناصر',
    ],
    'not_in' => ':attribute موجود',
    'not_regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => 'يجب على :attribute أن يكون رقمًا',
    'present' => 'يجب تقديم :attribute',
    'regex' => 'صيغة :attribute .غير صحيحة',
    'required' => ':attribute مطلوب.',
    'required_if' => ':attribute مطلوب في حال ما إذا كان :other يساوي :value.',
    'required_unless' => ':attribute مطلوب في حال ما لم يكن :other يساوي :values.',
    'required_with' => ':attribute مطلوب إذا توفّر :values.',
    'required_with_all' => ':attribute مطلوب إذا توفّر :values.',
    'required_without' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'required_without_all' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other',
    'size' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية لـ :size',
        'file' => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت',
        'string' => 'يجب أن يحتوي النص :attribute على :size حروفٍ/حرفًا بالضبط',
        'array' => 'يجب أن يحتوي :attribute على :size عنصرٍ/عناصر بالضبط',
    ],
    'string' => 'يجب أن يكون :attribute نصآ.',
    'timezone' => 'يجب أن يكون :attribute نطاقًا زمنيًا صحيحًا',
    'unique' => 'قيمة :attribute مُستخدمة من قبل',
    'uploaded' => 'فشل في تحميل الـ :attribute',
    'url' => 'صيغة الرابط :attribute غير صحيحة',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'nhc_mobile' => [
            'digits' => 'يجب أن يتكون رقم الجوال من 10 أرقام بالضبط.',
            'regex' => 'يجب أن يبدأ رقم الجوال بـ 05.',
        ],
        'whatsapp_number' => [
            'regex' => 'يجب إدخال رقم جوال صحيح (يبدأ بـ 05 ومكوّن من 10 أرقام).',
        ],
        'contact_numbers.*' => [
            'regex' => 'يجب إدخال رقم جوال صحيح (يبدأ بـ 05 ومكوّن من 10 أرقام).',
        ],
        'commercial_registration_file' => [
            'mimes' => 'يجب أن يكون الملف بصيغة jpg أو jpeg أو png أو pdf.',
        ],
        'cover_image' => [
            'mimes' => 'يجب أن تكون الصورة بصيغة jpg أو jpeg أو png.',
        ],
        'apartment_images.*' => [
            'mimes' => 'يجب أن تكون الصورة بصيغة jpg أو jpeg أو png.',
        ],
        'apartment_video' => [
            'mimes' => 'يجب أن يكون الفيديو بصيغة mp4 أو mov.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'الاسم',
        'username' => 'اسم المُستخدم',
        'email' => 'البريد الإلكتروني',
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'address' => 'عنوان السكن',
        'phone' => 'رقم الهاتف',
        'mobile' => 'الجوال',
        'country_code' => 'رمز الدولة',
        'avatar' => 'الصورة الشخصية',
        'is_active' => 'حالة التفعيل',
        'role_id' => 'الدور',
        'age' => 'العمر',
        'sex' => 'الجنس',
        'gender' => 'النوع',
        'day' => 'اليوم',
        'month' => 'الشهر',
        'year' => 'السنة',
        'hour' => 'ساعة',
        'minute' => 'دقيقة',
        'second' => 'ثانية',
        'title' => 'العنوان',
        'content' => 'المُحتوى',
        'description' => 'الوصف',
        'excerpt' => 'المُلخص',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'available' => 'مُتاح',
        'size' => 'الحجم',
        'type' => 'نوع الباقة',
        'ads_count' => 'عدد الإعلانات',
        'duration_days' => 'مدة الباقة (بالأيام)',
        'price' => 'السعر',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'max_subscribers' => 'الحد الأقصى للمشتركين',
        'name.en' => 'الاسم (بالإنجليزية)',
        'name.ar' => 'الاسم (بالعربية)',
        // Ad fields
        'fal_license_number' => 'رقم رخصة فال',
        'nhc_mobile' => 'رقم جوال نفاذ',
        'advertiser_type' => 'نوع المعلن',
        'commercial_registration_number' => 'رقم السجل التجاري',
        'commercial_registration_file' => 'ملف السجل التجاري',
        'ad_license_number' => 'رقم رخصة الإعلان',
        'purpose' => 'الغرض من الإعلان',
        'apartment_condition' => 'حالة الشقة',
        'deed_number' => 'رقم الصك',
        'living_rooms_count' => 'عدد الصالات',
        'bathrooms_count' => 'عدد الحمامات',
        'floor' => 'الطابق',
        'furnishing_status' => 'حالة التأثيث',
        'rental_period' => 'فترة الإيجار',
        'cover_image' => 'صورة الغلاف',
        'apartment_images' => 'صور الشقة',
        'apartment_images.*' => 'صورة الشقة',
        'apartment_video' => 'فيديو الشقة',
        // Auth / OTP fields
        'code' => 'رمز التحقق',
        'fcm_token' => 'رمز الجهاز',
        'device_type' => 'نوع الجهاز',
        'device_token' => 'رمز الجهاز',
        'identity_number' => 'رقم الهوية',
        'trans_id' => 'رقم المعاملة',
        'status' => 'الحالة',
        'reference' => 'المرجع',
        // Contact / message fields
        'message' => 'الرسالة',
        'message_type' => 'نوع الرسالة',
        'reply' => 'الرد',
        'reason' => 'السبب',
        'rating' => 'التقييم',
        'feedback' => 'التعليق',
        // Notification fields
        'title.en' => 'العنوان (بالإنجليزية)',
        'title.ar' => 'العنوان (بالعربية)',
        'body' => 'نص الإشعار',
        'body.en' => 'نص الإشعار (بالإنجليزية)',
        'body.ar' => 'نص الإشعار (بالعربية)',
        'user_ids' => 'المستخدمون',
        'user_ids.*' => 'معرّف المستخدم',
        // Package / subscription fields
        'package_id' => 'الباقة',
        'image' => 'الصورة',
        // Role / permission fields
        'permissions' => 'الصلاحيات',
        'permissions.*' => 'الصلاحية',
        // Settings fields
        'value_ar' => 'القيمة (بالعربية)',
        'value_en' => 'القيمة (بالإنجليزية)',
        'is_free_period_enabled' => 'تفعيل الفترة المجانية',
        'free_period_start_date' => 'تاريخ بدء الفترة المجانية',
        'free_period_end_date' => 'تاريخ انتهاء الفترة المجانية',
        'free_period_reason_ar' => 'سبب الفترة المجانية (بالعربية)',
        'free_period_reason_en' => 'سبب الفترة المجانية (بالإنجليزية)',
        // Contact setting fields
        'facebook_link' => 'رابط فيسبوك',
        'x_link' => 'رابط تويتر (X)',
        'instagram_link' => 'رابط إنستغرام',
        'snapchat_link' => 'رابط سناب شات',
        'tiktok_link' => 'رابط تيك توك',
        'youtube_link' => 'رابط يوتيوب',
        'whatsapp_number' => 'رقم واتساب',
        'contact_numbers' => 'أرقام التواصل',
        'contact_numbers.*' => 'رقم التواصل',
        // Blog fields
        'description.ar' => 'الوصف (بالعربية)',
        'description.en' => 'الوصف (بالإنجليزية)',
        'content.ar' => 'المحتوى (بالعربية)',
        'content.en' => 'المحتوى (بالإنجليزية)',
        'main_image_ar' => 'الصورة الرئيسية (عربي)',
        'main_image_en' => 'الصورة الرئيسية (إنجليزي)',
        'meta_title' => 'عنوان الميتا',
        'meta_title.ar' => 'عنوان الميتا (بالعربية)',
        'meta_title.en' => 'عنوان الميتا (بالإنجليزية)',
        'meta_description' => 'وصف الميتا',
        'meta_description.ar' => 'وصف الميتا (بالعربية)',
        'meta_description.en' => 'وصف الميتا (بالإنجليزية)',
        'image_alt' => 'النص البديل للصورة',
        'image_alt.ar' => 'النص البديل للصورة (بالعربية)',
        'image_alt.en' => 'النص البديل للصورة (بالإنجليزية)',
    ],
];
