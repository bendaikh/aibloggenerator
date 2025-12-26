# Subdomain-Based Website System

## 🎉 Implementation Complete!

Your AI Blog Generator now supports **subdomain-based websites** with the ability to later add custom domains!

## How It Works

### 1. **Subdomain System**
Each website gets its own subdomain:
- Example: `mysite.yourdomain.com`
- Automatically generated from the website name
- Unique for each website

### 2. **Custom Domain Support**
Users can later connect custom domains:
- Example: `www.customwebsite.com`
- Points to their website
- Configured in the website settings

## Setup Instructions

### Step 1: Configure Your Domain

Add this to your `.env` file:

```env
BASE_DOMAIN=localhost
# For production, change to:
# BASE_DOMAIN=yourdomain.com
```

### Step 2: DNS Configuration (Production Only)

When deploying to production, configure a wildcard DNS record:

**Add to your DNS provider:**
```
Type: A Record
Name: *
Value: YOUR_SERVER_IP
TTL: 3600
```

This allows all subdomains (like `mysite.yourdomain.com`) to point to your server.

### Step 3: Web Server Configuration

#### For Apache:
Add to your VirtualHost:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com
    DocumentRoot /path/to/aibloggenerator/public
    
    <Directory /path/to/aibloggenerator/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### For Nginx:
```nginx
server {
    listen 80;
    server_name yourdomain.com *.yourdomain.com;
    root /path/to/aibloggenerator/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Usage

### Creating a Website

1. **Go to:** Superadmin → Websites → Create New Website
2. **Fill in:**
   - Name: "My Food Blog"
   - Description: "Delicious recipes"
   - Social media links
3. **Click Create**

The system automatically:
- Generates subdomain: `my-food-blog`
- Creates URL: `http://my-food-blog.localhost` (dev) or `https://my-food-blog.yourdomain.com` (prod)
- Applies Solushcooks theme
- Creates default categories

### Accessing Websites

**Development (localhost):**
```
http://mysite.localhost:8000
```

**Production:**
```
https://mysite.yourdomain.com
```

### Adding Custom Domains

1. **Edit the website** in admin panel
2. **Add custom domain:** `www.customwebsite.com`
3. **Save**
4. **Configure DNS:** Add A record pointing `customwebsite.com` to your server
5. **Done!** Website accessible at both:
   - `https://mysite.yourdomain.com` (subdomain)
   - `https://www.customwebsite.com` (custom domain)

## Local Development Testing

### Testing Subdomains Locally

Since browsers don't naturally support `*.localhost` subdomains, you have two options:

#### Option 1: Modify hosts file (Windows)

Edit `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1  mysite.localhost
127.0.0.1  another-site.localhost
```

#### Option 2: Use `.test` domain with Laravel Valet (Mac/Linux)

Or continue using the `/site/{slug}` URLs for local development.

## Database Structure

### Websites Table
```sql
- id
- user_id
- name
- slug (for backward compatibility)
- subdomain (unique, for subdomain access)
- domain (nullable, for custom domain)
- description
- logo
- theme
- theme_settings (JSON)
- social_media (JSON)
- is_active
- timestamps
```

## Routes

### Admin Routes (yourdomain.com)
```
GET /superadmin/websites          - List websites
GET /superadmin/websites/create   - Create website
POST /superadmin/websites         - Store website
GET /superadmin/websites/{id}     - View website
```

### Public Routes (Subdomain/Custom Domain)
```
GET /                             - Website homepage
GET /category/{slug}              - Category page
GET /article/{slug}               - Article page
```

### Legacy Routes (Backward Compatibility)
```
GET /site/{website}                     - Website homepage
GET /site/{website}/category/{category}  - Category page
GET /site/{website}/article/{article}    - Article page
```

## Features

✅ **Automatic subdomain generation**
✅ **Unique subdomain validation**
✅ **Custom domain support**
✅ **Middleware for website identification**
✅ **Backward compatible with /site/ URLs**
✅ **Beautiful Solushcooks theme**
✅ **Multi-tenant architecture**

## Troubleshooting

### Subdomain not loading locally?
- Make sure you added it to your hosts file
- Or use `/site/{slug}` URL for development

### "Website not found" error?
- Check `BASE_DOMAIN` in `.env`
- Verify website is active in database
- Check subdomain matches database

### Custom domain not working?
- Verify DNS A record is configured
- Wait for DNS propagation (up to 48 hours)
- Check domain field in website settings

## Next Steps

1. **Deploy to production server**
2. **Configure wildcard DNS**
3. **Set up SSL certificates** (use Let's Encrypt with certbot)
4. **Enable custom domains** in website settings
5. **Start creating content!**

---

🚀 **Your subdomain-based multi-tenant blog system is ready!**

