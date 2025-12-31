<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    /**
     * Organization Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        $stats = [
            'totalWebsites' => $websites->count(),
            'totalArticles' => Article::whereIn('website_id', $websites->pluck('id'))->count(),
            'totalCategories' => Category::whereIn('website_id', $websites->pluck('id'))->count(),
            'totalPages' => Page::whereIn('website_id', $websites->pluck('id'))->count(),
        ];

        return Inertia::render('Organization/Dashboard', [
            'stats' => $stats,
            'websites' => $websites,
        ]);
    }

    /**
     * Global Settings Page
     */
    public function settings()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Settings', [
            'settings' => [
                'openai_api_key_set' => !empty($user->openai_api_key),
                'openai_api_key_masked' => $user->openai_api_key ? 'sk-....' . substr($user->openai_api_key, -4) : null,
                'ai_model' => $user->ai_model ?? 'gpt-4o',
                'ai_default_tone' => $user->ai_default_tone ?? 'conversational',
            ],
            'websites' => $websites,
        ]);
    }

    /**
     * Update Global Settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'openai_api_key' => 'nullable|string',
            'ai_model' => 'required|in:gpt-4o,gpt-4-turbo,gpt-3.5-turbo',
            'ai_default_tone' => 'required|in:conversational,professional,casual,friendly,formal',
        ]);

        $updateData = [
            'ai_model' => $validated['ai_model'],
            'ai_default_tone' => $validated['ai_default_tone'],
        ];

        if (!empty($validated['openai_api_key'])) {
            $updateData['openai_api_key'] = $validated['openai_api_key'];
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Test AI Connection
     */
    public function testAiConnection()
    {
        $user = Auth::user();

        if (empty($user->openai_api_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No API key configured'
            ]);
        }

        try {
            $client = \OpenAI::client($user->openai_api_key);
            $response = $client->models()->list();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! API key is valid.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Websites List
     */
    public function websitesIndex()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Index', [
            'websites' => $websites,
        ]);
    }

    /**
     * Create Website Form
     */
    public function websitesCreate()
    {
        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Create', [
            'websites' => $websites,
        ]);
    }

    /**
     * Store New Website
     */
    public function websitesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $user = Auth::user();

        // Generate subdomain if not provided
        if (empty($validated['subdomain'])) {
            $validated['subdomain'] = Str::slug($validated['name']);
            
            // Ensure uniqueness
            $originalSubdomain = $validated['subdomain'];
            $counter = 1;
            while (Website::where('subdomain', $validated['subdomain'])->exists()) {
                $validated['subdomain'] = $originalSubdomain . '-' . $counter;
                $counter++;
            }
        }

        $website = Website::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'domain' => $validated['domain'] ?? null,
            'subdomain' => $validated['subdomain'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create default pages for the website
        $this->createDefaultPages($website);

        return redirect()->route('superadmin.dashboard', ['website' => $website->id])
            ->with('success', 'Website created successfully!');
    }

    /**
     * Create default pages (About Us, Contact Us, Privacy Policy) for a new website.
     */
    private function createDefaultPages(Website $website): void
    {
        $aboutUsContent = $this->getAboutUsContent($website);
        $contactUsContent = $this->getContactUsContent($website);
        $privacyPolicyContent = $this->getPrivacyPolicyContent($website);

        $defaultPages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => $aboutUsContent,
                'excerpt' => 'Learn more about ' . $website->name . ' and our mission to deliver quality content.',
                'meta_title' => 'About Us - ' . $website->name,
                'meta_description' => 'Discover who we are and what drives us at ' . $website->name . '. Learn about our mission, values, and the team behind the content.',
                'order' => 1,
                'is_active' => true,
                'show_in_menu' => true,
                'is_default' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => $contactUsContent,
                'excerpt' => 'Get in touch with us. We would love to hear from you!',
                'meta_title' => 'Contact Us - ' . $website->name,
                'meta_description' => 'Contact ' . $website->name . ' for questions, feedback, or business inquiries. We are here to help!',
                'order' => 2,
                'is_active' => true,
                'show_in_menu' => true,
                'is_default' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $privacyPolicyContent,
                'excerpt' => 'Our commitment to protecting your privacy and personal information.',
                'meta_title' => 'Privacy Policy - ' . $website->name,
                'meta_description' => 'Read our Privacy Policy to understand how ' . $website->name . ' collects, uses, and protects your personal information.',
                'order' => 3,
                'is_active' => true,
                'show_in_menu' => false,
                'is_default' => true,
            ],
        ];

        foreach ($defaultPages as $pageData) {
            Page::create(array_merge($pageData, ['website_id' => $website->id]));
        }
    }

    /**
     * Get About Us page content with beautiful design
     */
    private function getAboutUsContent(Website $website): string
    {
        $name = $website->name;
        
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 20px; font-weight: 700;">Welcome to {$name}</h1>
    <p style="font-size: 1.25rem; opacity: 0.95; max-width: 600px; margin: 0 auto;">We're passionate about creating exceptional content that inspires, educates, and connects people from all walks of life.</p>
</div>

<!-- Our Story Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px; align-items: center;">
    <div>
        <h2 style="color: #1e293b; font-size: 1.8rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">📖</span>
            Our Story
        </h2>
        <p style="color: #475569; line-height: 1.8; font-size: 1.1rem;">
            Founded with a vision to democratize knowledge, {$name} has grown from a small passion project into a thriving community of readers and contributors. Every day, we strive to bring you content that matters.
        </p>
        <p style="color: #475569; line-height: 1.8; font-size: 1.1rem; margin-top: 15px;">
            Our journey began with a simple belief: everyone deserves access to quality information that can transform their lives. Today, we continue that mission with unwavering dedication.
        </p>
    </div>
    <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); padding: 40px; border-radius: 16px; border: 2px solid #99f6e4;">
        <div style="text-align: center;">
            <div style="font-size: 3rem; font-weight: 700; color: #0d9488;">1000+</div>
            <div style="color: #14b8a6; font-weight: 500;">Articles Published</div>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <div style="font-size: 3rem; font-weight: 700; color: #0d9488;">50K+</div>
            <div style="color: #14b8a6; font-weight: 500;">Happy Readers</div>
        </div>
    </div>
</div>

<!-- Our Values Section -->
<div style="background: #f8fafc; padding: 50px 40px; border-radius: 20px; margin-bottom: 50px;">
    <h2 style="text-align: center; color: #1e293b; font-size: 1.8rem; margin-bottom: 40px;">Our Core Values</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">🎯</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Quality First</h3>
            <p style="color: #64748b; font-size: 0.95rem;">We never compromise on the quality of our content. Every piece is carefully researched and crafted.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">💡</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Innovation</h3>
            <p style="color: #64748b; font-size: 0.95rem;">We embrace new ideas and technologies to deliver content in the most engaging ways possible.</p>
        </div>
        <div style="text-align: center; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="font-size: 2.5rem; margin-bottom: 15px;">🤝</div>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 10px;">Community</h3>
            <p style="color: #64748b; font-size: 0.95rem;">Our readers are at the heart of everything we do. We build for you, with you.</p>
        </div>
    </div>
</div>

<!-- What We Offer Section -->
<div style="margin-bottom: 50px;">
    <h2 style="color: #1e293b; font-size: 1.8rem; margin-bottom: 30px; text-align: center;">What We Offer</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">✨</div>
            <div>
                <h4 style="color: #92400e; font-weight: 600; margin-bottom: 5px;">Expert Articles</h4>
                <p style="color: #a16207; font-size: 0.9rem;">In-depth articles written by industry experts and passionate writers.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">📚</div>
            <div>
                <h4 style="color: #1e40af; font-weight: 600; margin-bottom: 5px;">Curated Resources</h4>
                <p style="color: #1d4ed8; font-size: 0.9rem;">Handpicked resources to help you learn and grow in your journey.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🎙️</div>
            <div>
                <h4 style="color: #6b21a8; font-weight: 600; margin-bottom: 5px;">Engaging Content</h4>
                <p style="color: #7c3aed; font-size: 0.9rem;">From tutorials to thought pieces, we cover topics that matter to you.</p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; padding: 25px; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-radius: 12px;">
            <div style="font-size: 1.5rem;">🌍</div>
            <div>
                <h4 style="color: #166534; font-weight: 600; margin-bottom: 5px;">Global Perspective</h4>
                <p style="color: #15803d; font-size: 0.9rem;">Content that embraces diversity and brings worldwide perspectives.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 50px 40px; border-radius: 20px; text-align: center; color: white;">
    <h2 style="font-size: 1.8rem; margin-bottom: 15px;">Ready to Explore?</h2>
    <p style="opacity: 0.9; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">Dive into our collection of articles and discover content that will inspire and inform you.</p>
    <a href="/" style="display: inline-block; background: #10b981; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.3s;">Browse Articles →</a>
</div>
HTML;
    }

    /**
     * Get Contact Us page content with contact form
     */
    private function getContactUsContent(Website $website): string
    {
        $name = $website->name;
        $email = 'contact@' . ($website->domain ?? $website->subdomain . '.example.com');
        
        return <<<HTML
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 50px 40px; border-radius: 20px; margin-bottom: 40px; text-align: center; color: white;">
    <h1 style="font-size: 2.5rem; margin-bottom: 15px; font-weight: 700;">Get in Touch</h1>
    <p style="font-size: 1.15rem; opacity: 0.95;">We'd love to hear from you! Reach out and let's start a conversation.</p>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
    
    <!-- Contact Form -->
    <div style="background: #f8fafc; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0;">
        <h2 style="color: #1e293b; font-size: 1.5rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.3rem;">✉️</span> Send Us a Message
        </h2>
        
        <form id="contact-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Name *</label>
                <input type="text" name="name" required placeholder="John Doe" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Email Address *</label>
                <input type="email" name="email" required placeholder="john@example.com" style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Subject *</label>
                <select name="subject" required style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; background: white; cursor: pointer; outline: none; box-sizing: border-box;">
                    <option value="">Select a topic...</option>
                    <option value="general">General Inquiry</option>
                    <option value="feedback">Feedback</option>
                    <option value="partnership">Partnership / Collaboration</option>
                    <option value="advertising">Advertising</option>
                    <option value="support">Technical Support</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; color: #475569; font-weight: 500; margin-bottom: 8px; font-size: 0.95rem;">Your Message *</label>
                <textarea name="message" required rows="5" placeholder="Write your message here..." style="width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem; resize: vertical; font-family: inherit; transition: border-color 0.3s; outline: none; box-sizing: border-box;" onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            </div>
            
            <button type="submit" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; padding: 16px 32px; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(14,165,233,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                Send Message →
            </button>
        </form>
        
        <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 15px; text-align: center;">We typically respond within 24-48 hours</p>
    </div>
    
    <!-- Contact Info & Other Ways -->
    <div>
        <!-- Direct Contact -->
        <div style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #99f6e4;">
            <h3 style="color: #0f766e; font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span>📧</span> Direct Contact
            </h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: white; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">📬</div>
                    <div>
                        <div style="color: #64748b; font-size: 0.85rem;">Email Us</div>
                        <a href="mailto:{$email}" style="color: #0f766e; font-weight: 600; text-decoration: none;">{$email}</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Response Time -->
        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 30px; border-radius: 16px; margin-bottom: 25px; border: 2px solid #fcd34d;">
            <h3 style="color: #92400e; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>⏰</span> Response Time
            </h3>
            <p style="color: #a16207; font-size: 0.95rem; line-height: 1.6;">
                We aim to respond to all inquiries within <strong>24-48 business hours</strong>. For urgent matters, please indicate so in your message subject.
            </p>
        </div>
        
        <!-- Business Inquiries -->
        <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); padding: 30px; border-radius: 16px; border: 2px solid #d8b4fe;">
            <h3 style="color: #6b21a8; font-size: 1.2rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <span>💼</span> Business Inquiries
            </h3>
            <p style="color: #7c3aed; font-size: 0.95rem; line-height: 1.6; margin-bottom: 15px;">
                Interested in partnerships, collaborations, or advertising opportunities? We're always open to working with brands that align with our values.
            </p>
            <ul style="color: #7c3aed; font-size: 0.9rem; padding-left: 20px; margin: 0;">
                <li style="margin-bottom: 8px;">Sponsored Content</li>
                <li style="margin-bottom: 8px;">Brand Partnerships</li>
                <li style="margin-bottom: 8px;">Advertising Opportunities</li>
                <li>Guest Contributions</li>
            </ul>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div style="background: #f8fafc; padding: 50px 40px; border-radius: 20px; margin-bottom: 40px;">
    <h2 style="text-align: center; color: #1e293b; font-size: 1.8rem; margin-bottom: 35px;">Frequently Asked Questions</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; max-width: 900px; margin: 0 auto;">
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">How quickly will I get a response?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">We typically respond within 24-48 business hours. Complex inquiries may take a bit longer.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Can I contribute articles?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Yes! We welcome guest contributors. Please reach out with your topic ideas and writing samples.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Do you offer advertising?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Yes, we offer various advertising options. Contact us for our media kit and pricing.</p>
        </div>
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h4 style="color: #1e293b; font-size: 1rem; margin-bottom: 10px;">Where are you located?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">We're a remote team working across multiple time zones to serve our global audience.</p>
        </div>
    </div>
</div>

<!-- Social Follow -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 40px; border-radius: 20px; text-align: center; color: white;">
    <h3 style="font-size: 1.4rem; margin-bottom: 10px;">Connect With Us</h3>
    <p style="opacity: 0.8; margin-bottom: 20px;">Follow us on social media for the latest updates and behind-the-scenes content.</p>
    <div style="display: flex; justify-content: center; gap: 15px;">
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📘</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">🐦</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">📸</a>
        <a href="#" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 12px; text-decoration: none; font-size: 1.5rem; transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">💼</a>
    </div>
</div>
HTML;
    }

    /**
     * Get Privacy Policy page content
     */
    private function getPrivacyPolicyContent(Website $website): string
    {
        $name = $website->name;
        $date = now()->format('F j, Y');
        
        return <<<HTML
<h2>Privacy Policy</h2>
<p><strong>Last updated:</strong> {$date}</p>

<p>At {$name}, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>

<h3>Information We Collect</h3>
<p>We may collect information about you in a variety of ways, including:</p>
<ul>
    <li><strong>Personal Data:</strong> Voluntarily provided information such as your name and email address when you subscribe to our newsletter or contact us.</li>
    <li><strong>Derivative Data:</strong> Information our servers automatically collect when you access the site, such as your IP address, browser type, and pages visited.</li>
    <li><strong>Cookies:</strong> We may use cookies and similar tracking technologies to enhance your experience on our site.</li>
</ul>

<h3>Use of Your Information</h3>
<p>We use the information we collect to:</p>
<ul>
    <li>Provide and maintain our website</li>
    <li>Improve user experience</li>
    <li>Send newsletters and updates (with your consent)</li>
    <li>Respond to your inquiries</li>
</ul>

<h3>Disclosure of Your Information</h3>
<p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as required by law or to protect our rights.</p>

<h3>Security of Your Information</h3>
<p>We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>

<h3>Contact Us</h3>
<p>If you have questions about this Privacy Policy, please contact us.</p>
HTML;
    }

    /**
     * Edit Website Form
     */
    public function websitesEdit(Website $website)
    {
        $this->authorize('update', $website);

        $user = Auth::user();
        $websites = Website::where('user_id', $user->id)
            ->withCount(['articles', 'categories'])
            ->get();

        return Inertia::render('Organization/Websites/Edit', [
            'website' => $website,
            'websites' => $websites,
        ]);
    }

    /**
     * Update Website
     */
    public function websitesUpdate(Request $request, Website $website)
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:websites,subdomain,' . $website->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $website->update($validated);

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website updated successfully!');
    }

    /**
     * Delete Website
     */
    public function websitesDestroy(Website $website)
    {
        $this->authorize('delete', $website);

        $website->delete();

        return redirect()->route('organization.websites.index')
            ->with('success', 'Website deleted successfully!');
    }
}

