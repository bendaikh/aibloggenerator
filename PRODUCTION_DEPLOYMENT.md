# Production Deployment Instructions

## Issue 1: Website Owner Role Not Available in Production

The "Website Owner" role needs to be created in your production database.

### Solution: Run the seeder in production

**SSH into your production server and run:**

```bash
cd /path/to/your/production/app
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This will create the "Website Owner" role and all necessary permissions.

### Verify it worked:

```bash
php artisan tinker
>>> \App\Models\Role::where('name', 'website_owner')->first();
```

You should see the Website Owner role.

## Issue 2: Website Isolation Fixed

**Changed behavior:**
- ✅ **Before**: Superadmin could see ALL websites (everyone's)
- ✅ **After**: ALL users (including superadmin) only see THEIR OWN websites

### What This Means:
- You (superadmin) will only see websites where `user_id` = your user ID
- Your partner (website_owner) will only see websites where `user_id` = their user ID
- Complete website isolation for all users
- No one can see other people's websites

### Files Changed:
- `app/Models/User.php`: Updated `canSeeAllWebsites()` to always return `false`

## After Production Deployment:

1. **Deploy the code changes** (includes the `role_id` fix in User model)

2. Run the seeder command:
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

3. Clear Laravel cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. Both you and your partner should logout and login again

5. Create new users with "Website Owner" role through the User Management interface

## Important Fix Applied:

Added `role_id` to the User model's `$fillable` array. This was preventing role updates in the User Management interface. Now you can:
- ✅ Create users with specific roles
- ✅ Update existing users' roles
- ✅ Role changes take effect immediately (after logout/login)

## Creating Website Owner Users:

1. Go to **User Management → Users**
2. Click **"Add User"**
3. Select **"Website Owner"** from role dropdown (will now be available after seeder runs)
4. The user will only see websites assigned to them (where `user_id` = their ID)

## Important Notes:

- Each user is now completely isolated to their own websites
- No user can see other users' websites, regardless of role
- Superadmin and Website Owner roles have the same website visibility (their own only)
- The only difference is Superadmin can manage users/roles, Website Owner cannot
