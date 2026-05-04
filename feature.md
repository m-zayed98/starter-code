# Feature Implementation Prompt: Advertising Packages Management

> **Target:** Laravel Application — Admin Panel API  
> **Scope:** Full CRUD + toggle + search for advertising packages  
> **Auth:** Admin middleware required on all routes

---

## Overview

Implement an **Advertising Packages Management** feature for a system admin. The admin can create, view, edit, delete, and toggle advertising packages. There are two package types: **normal** and **offer**, each with different fields and display behavior in the user-facing app.

---

## Database

Table name: `ad_packages`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `type` | enum | `normal`, `offer` |
| `name` | string | |
| `ads_count` | integer | |
| `duration_days` | integer | |
| `price` | decimal | SAR currency |
| `is_active` | boolean | default: `true` |
| `start_date` | date | nullable, offer only |
| `end_date` | date | nullable, offer only |
| `max_subscribers` | integer | nullable, offer only |
| `created_at` / `updated_at` | timestamps | |

There is a related `subscriptions` table linking users to packages. An **active subscription** means not expired and not cancelled. The package must check this relation before deletion.

---

## Required API Endpoints

All routes are protected by admin auth middleware. The project handles routing prefixes — define only the resource name and action.

---

### `ad-packages` — List (index)

**Purpose:** Return paginated/full list of packages with optional search.

**Query params:**
- `search` *(optional)* — filter by `name` (partial/LIKE match)

**Response fields per item:**
`id`, `name`, `type`, `created_at`, `price`, `ads_count`, `is_active`

**Ordering:** Offer packages first, then by `created_at` DESC.

**Empty states:**
- No packages at all → message key: `no_packages`
- Search yields nothing → message key: `no_search_results`

---

### `ad-packages/{id}` — Show (show)

**Purpose:** Full details of a single package.

**Response fields:**
`id`, `name`, `type`, `created_at`, `ads_count`, `price`, `duration_days`, `is_active`, `subscribers_count` (count of active subscriptions only)

**Additionally for offer type:**
`start_date`, `end_date`, `max_subscribers`

**Error:** 404 if package not found.

---

### `ad-packages` — Create (store)

**Purpose:** Create a new package (normal or offer).

**Request body & validation rules:**

| Field | Type | Rules |
|-------|------|-------|
| `type` | string | required, `in:normal,offer` |
| `name` | string | required, min:5, max:50 |
| `ads_count` | integer | required, min:1 (no zeros, no negatives) |
| `duration_days` | integer | required, min:1, max:1000 |
| `price` | decimal/float | required, min:0.01 (no zeros, no negatives) |
| `start_date` | date | required if `type=offer`, must be >= today |
| `end_date` | date | required if `type=offer`, must be strictly after `start_date` |
| `max_subscribers` | integer | required if `type=offer`, min:1 |

**Validation error messages (Arabic):**

| Condition | Message |
|-----------|---------|
| Any required field left empty | `"هذا الحقل مطلوب"` |
| `name` length outside 5–50 | `"يجب ان يكون اسم الباقه ما بين 5-50 حرف"` |
| `ads_count` = 0 | `"لا يمكن أن تكون قيمة الحقل مساوية للصفر"` |
| `ads_count` < 0 | `"لا يمكن أن تكون قيمة الحقل سالبة"` |
| `price` = 0 | `"لا يمكن أن تكون قيمة الحقل مساوية للصفر"` |
| `price` < 0 | `"لا يمكن أن تكون قيمة الحقل سالبة"` |

**Success:** 201 with created package data.

---

### `ad-packages/{id}` — Update (update)

**Purpose:** Edit an existing package. Only these fields are editable:

- `name` — same rules as creation
- `ads_count` — same rules as creation
- `price` — same rules as creation

Apply the same validation messages as the create endpoint.

**Error:** 404 if package not found.

---

### `ad-packages/{id}/toggle` — Toggle Active State (toggle)

**Purpose:** Flip `is_active` between `true` and `false`.

**Business logic:**
- **Deactivating:** Package disappears from user app and stops accepting new subscriptions. Users who already have an active subscription on this package are **not affected** — do not cancel their subscriptions.
- **Activating:** Package becomes visible again in the app.

**Response:** Return the updated `is_active` value.

---

### `ad-packages/{id}` — Delete (destroy)

**Purpose:** Permanently delete a package.

**Business logic:**
- Before deleting, check if the package has any **active subscriptions**.
- If active subscriptions exist → return HTTP 422 with message:  
  `"هذه الباقه مرتبطه بعدد من الاشتراكات الجارية لذا لا يمكن حذفها"`
- If no active subscriptions → delete and return 200.

**Error:** 404 if package not found.

---

## Business Rules

1. **Offer packages appear first** in all list responses (order by type = `offer` first).

2. **Offer package auto-hide conditions** — the package should not be visible to users in the app if any of these are true (implement via query scopes or scheduled jobs):
   - `is_active = false`
   - `end_date` has passed (today > `end_date`)
   - Active subscriber count has reached `max_subscribers`

3. **Normal package hide condition:** only when `is_active = false`.

4. `subscribers_count` in the detail endpoint must count only active (non-expired, non-cancelled) subscriptions.

5. `price` is stored as decimal and represents Saudi Riyals (SAR).

6. `start_date` for offer packages defaults to today and can only be today or a future date.

7. A package is **visible in the app the moment it is created** (for normal type). For offer type, it is visible immediately but subscriptions are only accepted starting from `start_date`.

---

## Edge Cases to Handle

| Case | Expected behavior |
|------|-------------------|
| No packages in DB | Return empty array + `no_packages` message |
| Search with no matches | Return empty array + `no_search_results` message |
| `GET /ad-packages/{id}` with invalid ID | 404 |
| Delete package with 0 active subscriptions | Allow deletion |
| Delete package with active subscriptions | 422 + Arabic error message |
| Toggle inactive while subscriptions are active | Deactivate package, keep subscriptions intact |
| `start_date` in the past on offer creation | Validation error |
| `end_date` equal to `start_date` | Validation error — must be strictly after |
| `ads_count` or `price` = 0 | Specific Arabic zero-value error |
| `ads_count` or `price` negative | Specific Arabic negative-value error |
| `name` under 5 or over 50 chars | Specific Arabic length error |

---

## Code Structure Guidelines

- Use **Form Request classes** for validation with Arabic custom messages.
- Use **API Resource classes** for response shaping (list resource vs. detail resource).
- Apply **ordering logic** (offer first) as a model scope or in the repository layer.
- Admin authorization must be enforced at **middleware level**, not inside controllers.
- The project has a custom Response facade that handles consistent response formatting — use it for all responses, do not build a custom wrapper.
- Use appropriate HTTP status codes: `200`, `201`, `404`, `422`.
- Separate list fields from detail fields using different API resources.

---

## Summary of What Needs to Be Built

- [ ] Migration for `ad_packages` table
- [ ] `AdPackage` model with scopes (active, offer-first ordering, visible)
- [ ] `AdPackageController` with: `index`, `show`, `store`, `update`, `toggle`, `destroy`
- [ ] Form Request: `StoreAdPackageRequest`
- [ ] Form Request: `UpdateAdPackageRequest`
- [ ] API Resource: `AdPackageListResource`
- [ ] API Resource: `AdPackageDetailResource`
- [ ] Route definitions: `index`, `show`, `store`, `update`, `toggle`, `destroy` on the `ad-packages` resource
- [ ] Relation check on delete (active subscriptions guard)
- [ ] Toggle logic that preserves existing subscriptions