# SQL Ambiguous Column Fix - User::servicesForClinic()

## Issue Description
**Error**: `SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'id' in field list is ambiguous`

**Location**: `app/Models/User.php` - `servicesForClinic()` method

## Root Cause
The `servicesForClinic()` method was generating SQL queries with ambiguous column references:

```sql
select `id` from `services` 
inner join `clinic_doctor_services` on `services`.`id` = `clinic_doctor_services`.`service_id` 
where `clinic_doctor_services`.`doctor_id` = 24 and `clinic_doctor_services`.`clinic_id` = 1
```

The `id` column exists in both:
- `services` table (primary key)
- `clinic_doctor_services` table (pivot table primary key)

When Laravel tried to select `id`, MySQL couldn't determine which table's `id` column to use.

## Solution Applied
**Before**:
```php
public function servicesForClinic($clinicId)
{
    return $this->services()->wherePivot('clinic_id', $clinicId);
}
```

**After**:
```php
public function servicesForClinic($clinicId)
{
    return $this->services()
        ->wherePivot('clinic_id', $clinicId)
        ->select('services.*'); // Explicitly select from services table
}
```

## Impact Fixed
This fix resolves SQL errors in the following locations:
1. **Doctor Index View**: `resources/views/secretary/doctors/index.blade.php`
   - Line 74: `$doctor->servicesForClinic(...)->pluck('id')`
   - Line 116: `$doctor->servicesForClinic(...)->get()`

2. **Doctor Controller**: `app/Http/Controllers/Secretary/DoctorController.php`
   - Line 182: `$doctor->servicesForClinic(...)->pluck('id')`

3. **Any future usage** of the `servicesForClinic()` method

## Technical Details
- **Root Table**: `services` - contains service definitions
- **Pivot Table**: `clinic_doctor_services` - links doctors to services per clinic
- **Relationship**: Many-to-many with clinic context
- **Fix**: Explicit column selection prevents ambiguous SQL queries

## Testing
✅ Doctor index page now loads without SQL errors
✅ Service badges display correctly in card layout
✅ All existing functionality preserved
✅ Method usages across codebase fixed

## Files Modified
- `app/Models/User.php` - Updated `servicesForClinic()` method
