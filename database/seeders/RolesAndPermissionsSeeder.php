<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'View user list and details', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new users', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Edit existing users', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'group' => 'users'],
            
            // Role Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'View role list and details', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Edit existing roles', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete roles', 'group' => 'roles'],
            
            // Permission Management
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'description' => 'View permission list', 'group' => 'permissions'],
            ['name' => 'permissions.assign', 'display_name' => 'Assign Permissions', 'description' => 'Assign permissions to roles', 'group' => 'permissions'],
            
            // Website Management
            ['name' => 'websites.view', 'display_name' => 'View Websites', 'description' => 'View website list and details', 'group' => 'websites'],
            ['name' => 'websites.create', 'display_name' => 'Create Websites', 'description' => 'Create new websites', 'group' => 'websites'],
            ['name' => 'websites.edit', 'display_name' => 'Edit Websites', 'description' => 'Edit existing websites', 'group' => 'websites'],
            ['name' => 'websites.delete', 'display_name' => 'Delete Websites', 'description' => 'Delete websites', 'group' => 'websites'],
            
            // Article Management
            ['name' => 'articles.view', 'display_name' => 'View Articles', 'description' => 'View article list and details', 'group' => 'articles'],
            ['name' => 'articles.create', 'display_name' => 'Create Articles', 'description' => 'Create new articles', 'group' => 'articles'],
            ['name' => 'articles.edit', 'display_name' => 'Edit Articles', 'description' => 'Edit existing articles', 'group' => 'articles'],
            ['name' => 'articles.delete', 'display_name' => 'Delete Articles', 'description' => 'Delete articles', 'group' => 'articles'],
            ['name' => 'articles.publish', 'display_name' => 'Publish Articles', 'description' => 'Publish and unpublish articles', 'group' => 'articles'],
            
            // AI Article Generation
            ['name' => 'ai-articles.generate', 'display_name' => 'Generate AI Articles', 'description' => 'Generate articles using AI', 'group' => 'ai'],
            ['name' => 'ai-articles.view', 'display_name' => 'View AI Jobs', 'description' => 'View AI generation jobs', 'group' => 'ai'],
            
            // Category Management
            ['name' => 'categories.view', 'display_name' => 'View Categories', 'description' => 'View category list', 'group' => 'categories'],
            ['name' => 'categories.create', 'display_name' => 'Create Categories', 'description' => 'Create new categories', 'group' => 'categories'],
            ['name' => 'categories.edit', 'display_name' => 'Edit Categories', 'description' => 'Edit existing categories', 'group' => 'categories'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Categories', 'description' => 'Delete categories', 'group' => 'categories'],
            
            // Page Management
            ['name' => 'pages.view', 'display_name' => 'View Pages', 'description' => 'View page list', 'group' => 'pages'],
            ['name' => 'pages.create', 'display_name' => 'Create Pages', 'description' => 'Create new pages', 'group' => 'pages'],
            ['name' => 'pages.edit', 'display_name' => 'Edit Pages', 'description' => 'Edit existing pages', 'group' => 'pages'],
            ['name' => 'pages.delete', 'display_name' => 'Delete Pages', 'description' => 'Delete pages', 'group' => 'pages'],
            
            // Author Management
            ['name' => 'authors.view', 'display_name' => 'View Authors', 'description' => 'View author list', 'group' => 'authors'],
            ['name' => 'authors.create', 'display_name' => 'Create Authors', 'description' => 'Create new authors', 'group' => 'authors'],
            ['name' => 'authors.edit', 'display_name' => 'Edit Authors', 'description' => 'Edit existing authors', 'group' => 'authors'],
            ['name' => 'authors.delete', 'display_name' => 'Delete Authors', 'description' => 'Delete authors', 'group' => 'authors'],
            
            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'View website settings', 'group' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'description' => 'Edit website settings', 'group' => 'settings'],
            ['name' => 'appearance.edit', 'display_name' => 'Edit Appearance', 'description' => 'Edit website appearance', 'group' => 'settings'],
            
            // Social Media
            ['name' => 'social-media.view', 'display_name' => 'View Social Media', 'description' => 'View social media settings', 'group' => 'social-media'],
            ['name' => 'social-media.edit', 'display_name' => 'Edit Social Media', 'description' => 'Edit social media settings', 'group' => 'social-media'],
            
            // Assets
            ['name' => 'assets.view', 'display_name' => 'View Assets', 'description' => 'View and manage assets', 'group' => 'assets'],
            ['name' => 'assets.upload', 'display_name' => 'Upload Assets', 'description' => 'Upload new assets', 'group' => 'assets'],
            ['name' => 'assets.delete', 'display_name' => 'Delete Assets', 'description' => 'Delete assets', 'group' => 'assets'],
            
            // Subscribers
            ['name' => 'subscribers.view', 'display_name' => 'View Subscribers', 'description' => 'View email subscribers', 'group' => 'subscribers'],
            ['name' => 'subscribers.export', 'display_name' => 'Export Subscribers', 'description' => 'Export subscriber list', 'group' => 'subscribers'],
            ['name' => 'subscribers.delete', 'display_name' => 'Delete Subscribers', 'description' => 'Delete subscribers', 'group' => 'subscribers'],
            
            // Deployment
            ['name' => 'deployment.view', 'display_name' => 'View Deployment', 'description' => 'View deployment settings', 'group' => 'deployment'],
            ['name' => 'deployment.manage', 'display_name' => 'Manage Deployment', 'description' => 'Manage website deployment', 'group' => 'deployment'],
            
            // Ads
            ['name' => 'ads.view', 'display_name' => 'View Ads', 'description' => 'View ads settings', 'group' => 'ads'],
            ['name' => 'ads.manage', 'display_name' => 'Manage Ads', 'description' => 'Manage ads settings', 'group' => 'ads'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Roles
        $superadmin = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Full access to all features and settings'
            ]
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Access to most features except user and role management'
            ]
        );

        $editor = Role::firstOrCreate(
            ['name' => 'editor'],
            [
                'display_name' => 'Editor',
                'description' => 'Can create and edit content but cannot publish or manage settings'
            ]
        );

        $viewer = Role::firstOrCreate(
            ['name' => 'viewer'],
            [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to view content'
            ]
        );

        $websiteOwner = Role::firstOrCreate(
            ['name' => 'website_owner'],
            [
                'display_name' => 'Website Owner',
                'description' => 'Full access to own websites and content, but cannot manage other users or see all websites'
            ]
        );

        // Assign all permissions to superadmin
        $allPermissions = Permission::all();
        $superadmin->permissions()->sync($allPermissions->pluck('id'));

        // Assign specific permissions to admin (everything except user/role/permission management)
        $adminPermissions = Permission::whereNotIn('group', ['users', 'roles', 'permissions'])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Assign specific permissions to editor (content management only)
        $editorPermissions = Permission::whereIn('group', [
            'articles', 'categories', 'pages', 'authors', 'ai', 'assets'
        ])->whereNotIn('name', [
            'articles.delete', 
            'articles.publish',
            'categories.delete',
            'pages.delete',
            'authors.delete',
            'assets.delete'
        ])->get();
        $editor->permissions()->sync($editorPermissions->pluck('id'));

        // Assign view-only permissions to viewer
        $viewerPermissions = Permission::where('name', 'like', '%.view')->get();
        $viewer->permissions()->sync($viewerPermissions->pluck('id'));

        // Assign all permissions to website owner EXCEPT global user/role/permission management
        $websiteOwnerPermissions = Permission::whereNotIn('group', ['users', 'roles', 'permissions'])->get();
        $websiteOwner->permissions()->sync($websiteOwnerPermissions->pluck('id'));

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
