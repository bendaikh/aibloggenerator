# Login As User (User Impersonation) Feature

## Overview

The "Login As User" feature allows administrators to temporarily login as another user to view the system from their perspective. This is useful for:
- Troubleshooting user issues
- Testing user permissions
- Providing customer support
- Verifying user experience

## ✅ Features Implemented

### 1. **Login As Button**
- Added a blue user icon button in the Actions column of the Users table
- Appears for all users in User Management
- Shows tooltip "Login as this user" on hover

### 2. **Impersonation Banner**
- **Prominent warning banner** displayed at the top when impersonating
- Shows current user name being impersonated
- **"Stop Impersonation" button** to return to your account
- Orange/Red gradient to clearly indicate impersonation mode

### 3. **Session Management**
- Original admin user ID stored in session
- Secure login/logout handling
- Automatic cleanup when stopping impersonation

### 4. **Safety Features**
- ✅ Cannot impersonate yourself (blocked with error message)
- ✅ Confirmation dialog before impersonating
- ✅ Clear visual indicator when impersonating
- ✅ Easy way to stop impersonation and return

## How to Use

### To Login As Another User

1. Go to **User Management** → **Users**
2. Find the user you want to login as
3. Click the **blue user icon** in the Actions column
4. Confirm the action in the popup dialog
5. You'll be redirected to the dashboard **logged in as that user**

### While Impersonating

- **Orange/red banner** appears at the top showing:
  - "You are currently logged in as [User Name]"
  - "You are viewing this account as an administrator"
- You can browse the system as if you were that user
- All actions are performed as that user

### To Stop Impersonating

**Method 1: Use the Banner**
- Click **"Stop Impersonation"** button in the orange banner

**Method 2: Manual Confirmation**
- A confirmation dialog will ask if you're sure
- Click "OK" to return to your admin account

## Technical Implementation

### Controller Methods

**File: `app/Http/Controllers/UserManagementController.php`**

#### `loginAs(User $user)`
- Prevents self-impersonation
- Stores original admin ID in session (`impersonator_id`)
- Logs in as target user
- Redirects to dashboard with success message

#### `stopImpersonation()`
- Retrieves original admin ID from session
- Clears impersonation session data
- Logs back in as original admin
- Redirects back to User Management

### Routes

**File: `routes/web.php`**

```php
Route::post('/users/{user}/login-as', [UserManagementController::class, 'loginAs'])
    ->name('organization.users.login-as');
    
Route::post('/users/stop-impersonation', [UserManagementController::class, 'stopImpersonation'])
    ->name('organization.users.stop-impersonation');
```

### Frontend Components

**File: `resources/js/Pages/SuperAdmin/UserManagement/Users.vue`**
- Added `loginAsUser()` method
- Added blue user icon button in Actions column
- Confirmation dialog before impersonation

**File: `resources/js/Layouts/OrganizationLayout.vue`**
- Added impersonation detection from shared props
- Added prominent banner at top of main content
- Added `stopImpersonation()` method
- Imported `router` from Inertia

### Middleware Integration

**File: `app/Http/Middleware/HandleInertiaRequests.php`**

Shares impersonation status with all Inertia views:

```php
'isImpersonating' => $request->session()->has('impersonator_id'),
```

## Security Considerations

### ✅ Implemented Safety Measures

1. **Self-impersonation blocked** - Admins cannot impersonate themselves
2. **Confirmation required** - Dialog confirms the action before proceeding
3. **Visual indicator** - Impossible to forget you're impersonating
4. **Session-based** - Uses secure Laravel session management
5. **Easy exit** - One-click return to admin account

### ⚠️ Important Notes

- **Audit logging**: Consider adding audit logs to track impersonation events
- **Permissions**: Currently available to all users with access to User Management
- **Timeout**: Impersonation session follows Laravel's default session timeout
- **Multiple windows**: Opening multiple tabs/windows will all be in impersonation mode

## Future Enhancements (Optional)

### Potential Improvements

1. **Audit Trail**
   - Log all impersonation events
   - Track what actions were performed while impersonating
   - Show impersonation history

2. **Time Limits**
   - Auto-logout after X minutes of impersonation
   - Require re-authentication to impersonate

3. **Permission-based Access**
   - Restrict to superadmin role only
   - Add specific "can impersonate" permission

4. **Activity Logging**
   - Mark all actions performed during impersonation
   - Show "performed by Admin (as User)" in logs

5. **Notification**
   - Optionally notify user when admin logs in as them
   - Compliance with privacy regulations

## Testing Checklist

- [x] Can click "Login As" button on any user
- [x] Cannot impersonate yourself (shows error)
- [x] Confirmation dialog appears before impersonation
- [x] Successfully logs in as target user
- [x] Orange banner appears at top
- [x] Banner shows correct user name
- [x] Can navigate as impersonated user
- [x] "Stop Impersonation" button works
- [x] Successfully returns to admin account
- [x] Returns to User Management page
- [x] Success message shown after stopping

## Files Modified

### Backend
1. `app/Http/Controllers/UserManagementController.php` - Added impersonation methods
2. `app/Http/Middleware/HandleInertiaRequests.php` - Share impersonation status
3. `routes/web.php` - Added impersonation routes

### Frontend
1. `resources/js/Pages/SuperAdmin/UserManagement/Users.vue` - Added button and method
2. `resources/js/Layouts/OrganizationLayout.vue` - Added banner and stop method

### Build
- ✅ Frontend compiled: `public/build/assets/Users-Cdf6fqKV.js`
- ✅ Frontend compiled: `public/build/assets/OrganizationLayout-Cpd3u-rw.js`

## Usage Example

### Scenario: Support Ticket

**Problem**: User reports they can't see their articles

**Solution using Login As**:
1. Navigate to User Management
2. Find the user
3. Click "Login As" icon
4. View their dashboard
5. Check what they see
6. Identify the issue
7. Click "Stop Impersonation"
8. Fix the issue as admin

---

**The feature is now live and ready to use!** 🎉

Access it from: **Organization → User Management → Users → Click the blue user icon**
