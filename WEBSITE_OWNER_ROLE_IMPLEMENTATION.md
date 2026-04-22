# Website Owner Role Implementation

## Overview
This implementation adds a new "Website Owner" role that allows you to have multiple admin users where each admin only sees and manages their own websites, not all websites in the system.

## Roles Structure

### 1. Super Administrator (superadmin)
- **Access**: Can ONLY see websites where `user_id` matches their account (same as Website Owner)
- **Permissions**: Full access including user management, role management, and permission management
- **Use case**: Platform owner who needs to manage users and roles

### 2. Website Owner (website_owner)
- **Access**: Can ONLY see websites where `user_id` matches their account
- **Permissions**: Full admin powers (create websites, articles, manage settings, etc.) BUT cannot manage users, roles, or permissions
- **Use case**: Business partners or content managers who need full website control without user management access

**Important**: Both Superadmin and Website Owner have the SAME website visibility - they only see their own websites. The difference is only in the ability to manage users/roles/permissions.

### 3. Administrator (admin)
- Similar to Website Owner but without full permissions

### 4. Editor (editor)
- Content management only

### 5. Viewer (viewer)
- Read-only access

## How It Works

### Technical Implementation

1. **User Model** (`app/Models/User.php`):
   - Added `isWebsiteOwner()` method to check if user has website_owner role
   - Added `canSeeAllWebsites()` method - now always returns `false` (all users see only their own websites)

2. **Controllers Updated**:
   - `WebsiteController`: Filters websites based on user_id
   - `OrganizationController`: All methods filter websites by user_id

3. **Query Pattern**:
   ```php
   // ALL users only see their own websites
   $websitesQuery = $user->canSeeAllWebsites() 
       ? Website::query()  // This never happens now (canSeeAllWebsites always returns false)
       : Website::where('user_id', $user->id);
   
   // In practice, this is now:
   $websites = Website::where('user_id', $user->id)->get();
   ```

4. **Website Isolation**: Complete isolation - no user can see other users' websites, regardless of role.

## How to Create Your Partner's Account

### Step 1: Login as Superadmin
Make sure you're logged in with your superadmin account.

### Step 2: Go to User Management
Navigate to: **User Management → Users**

### Step 3: Create New User
1. Click "Add User"
2. Fill in the details:
   - **Name**: Your partner's name
   - **Email**: Your partner's email
   - **Password**: Set a secure password
   - **Role**: Select **"Website Owner"** from the dropdown
3. Click "Create User"

### Step 4: Create a Website for Your Partner
1. Go to **Websites** or **Organization → Websites**
2. Click "Create New Website"
3. Fill in website details
4. **Important**: Make sure to either:
   - Login as your partner (using "Login As" feature)
   - OR manually update the website's `user_id` in the database to your partner's user ID

### Step 5: Your Partner Logs In
When your partner logs in with their account:
- They will ONLY see websites where `user_id` matches their account
- They have full admin powers for their websites
- They cannot see your websites or other users' websites
- They cannot manage users, roles, or permissions
- **They can access ALL themes (including private ones)** - perfect for business partners who need access to premium themes

## Theme Access for Website Owners

Website Owners have the same theme access as Superadmins:

### What They Can See
- ✅ All public themes
- ✅ All private themes
- ✅ All theme features and settings

### Why This Matters
If you have premium/private themes that you want to keep exclusive, Website Owners will still have access to them. This is intentional because:
- Business partners should have access to all available tools
- They can only use these themes on THEIR websites (they can't see others' websites anyway)
- Prevents limitations in creativity and design choices

### Restricting Theme Access
If you need to restrict theme access in the future, you can:
1. Create a new custom role with limited permissions
2. Update the theme query logic in `OrganizationController.php`
3. Look for lines with: `if (!$user->isSuperAdmin() && !$user->isWebsiteOwner())`

## Example Scenario

**You (Superadmin)**:
- Email: admin@example.com
- Role: Super Administrator
- Can see: ONLY YOUR websites (where `user_id` = your ID)
- Can manage: Users, roles, permissions, your websites

**Your Partner (Website Owner)**:
- Email: partner@example.com
- Role: Website Owner
- Can see: ONLY THEIR websites (where `user_id` = their ID)
- Cannot see: Your websites or manage other users

**Website Isolation**: Both users are completely isolated - you cannot see each other's websites. Each user manages their own portfolio independently.

## Permissions Comparison

| Permission Group | Superadmin | Website Owner |
|-----------------|------------|---------------|
| Users Management | ✅ | ❌ |
| Roles Management | ✅ | ❌ |
| Permissions Management | ✅ | ❌ |
| Websites Management | ✅ (All) | ✅ (Own only) |
| Articles Management | ✅ | ✅ |
| AI Article Generation | ✅ | ✅ |
| Categories | ✅ | ✅ |
| Pages | ✅ | ✅ |
| Authors | ✅ | ✅ |
| Settings | ✅ | ✅ |
| Social Media | ✅ | ✅ |
| Assets | ✅ | ✅ |
| Subscribers | ✅ | ✅ |
| Deployment | ✅ | ✅ |
| Ads | ✅ | ✅ |
| **Themes (All including Private)** | ✅ | ✅ |

## Database Changes

The role has been automatically created by running the seeder. No database migration is needed.

To verify the role was created:
```bash
php artisan tinker
\App\Models\Role::where('name', 'website_owner')->first();
```

## Security Notes

1. **Website Isolation**: Website Owners can ONLY see websites where they are the owner (`user_id` matches)
2. **Cannot Create Other Users**: Website Owners cannot access User Management
3. **Cannot Modify Roles**: Website Owners cannot change their own role or others'
4. **Authorization**: All website operations are still protected by Laravel's authorization policies
5. **Theme Access**: Website Owners have access to ALL themes (including private ones) - same as Superadmins

## Troubleshooting

### Role not showing in production
- Run the seeder in production: `php artisan db:seed --class=RolesAndPermissionsSeeder`
- Clear cache: `php artisan cache:clear && php artisan config:clear`

### User sees other people's websites
- This should NEVER happen now - all users are isolated
- Check the website's `user_id` field
- Make sure user logged out and logged in after role changes

### Partner cannot create websites
- Make sure they have the "Website Owner" role
- Check that permissions are properly assigned to the role
- Verify: `php artisan tinker` then `\App\Models\Role::with('permissions')->where('name', 'website_owner')->first()`

### Website doesn't show for user
- Check the website's `user_id` field - it should match the user's ID
- Query: `SELECT id, name, user_id FROM websites WHERE user_id = [user_id];`

### Seeder fails in production
- Make sure all migration are run: `php artisan migrate`
- Check database connection
- Run with verbose: `php artisan db:seed --class=RolesAndPermissionsSeeder -vvv`

## Next Steps

1. Create your partner's account via User Management
2. Create or assign websites to your partner
3. Test by logging in as your partner using "Login As" feature
4. Verify they only see their websites

## Files Modified

- `database/seeders/RolesAndPermissionsSeeder.php`: Added website_owner role
- `app/Models/User.php`: Added isWebsiteOwner() and canSeeAllWebsites() methods
- `app/Http/Controllers/WebsiteController.php`: Updated to filter by user role
- `app/Http/Controllers/OrganizationController.php`: Updated all methods to filter by user role + theme access for Website Owners
- `app/Http/Controllers/UserManagementController.php`: Updated to set status='approved' on user creation

## Theme Access Implementation

The following locations were updated to grant Website Owners access to all themes (including private):

1. **OrganizationController::themes()** (line ~312)
2. **OrganizationController::websitesCreate()** (line ~664)
3. **OrganizationController::globalArticlesThemeSelect()** (line ~1499)

Changed from:
```php
if (!$user->isSuperAdmin()) {
    $themesQuery->where('is_public', true);
}
```

To:
```php
if (!$user->isSuperAdmin() && !$user->isWebsiteOwner()) {
    $themesQuery->where('is_public', true);
}
```

This ensures Website Owners have the same theme access as Superadmins.
