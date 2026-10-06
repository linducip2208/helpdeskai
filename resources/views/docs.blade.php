<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Documentation &amp; Tutorial</h2>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <h1 class="card-title fs-2">HelpDesk AI Docs</h1>
            <p class="text-secondary mb-0">Quick start, fitur lengkap, dan kredensial akses demo. Cocok untuk evaluasi maupun saat onboarding tim.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title">Akses Demo</h2>
            <p class="text-secondary mb-2">Semua akun di-seed otomatis. Password sama untuk semua: <code>password</code></p>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Akses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Admin</strong></td>
                            <td><code>admin@helpdesk.test</code></td>
                            <td><code>password</code></td>
                            <td>Full admin panel di <code>/admin</code></td>
                        </tr>
                        <tr>
                            <td><strong>Agent</strong></td>
                            <td><code>agent@helpdesk.test</code></td>
                            <td><code>password</code></td>
                            <td>Tickets, conversations, knowledge base</td>
                        </tr>
                        <tr>
                            <td><strong>Customer</strong></td>
                            <td><code>customer@helpdesk.test</code></td>
                            <td><code>password</code></td>
                            <td>Dashboard user, submit ticket</td>
                        </tr>
                        <tr>
                            <td><strong>Bulk Customer</strong></td>
                            <td><code>customer0@demo.test &hellip; customer299@demo.test</code></td>
                            <td><code>password</code></td>
                            <td>300 akun customer untuk uji pagination &amp; load</td>
                        </tr>
                        <tr>
                            <td><strong>Bulk Agent</strong></td>
                            <td><code>agent0@demo.test &hellip; agent39@demo.test</code></td>
                            <td><code>password</code></td>
                            <td>40 akun agent untuk uji assignment &amp; workload</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="text-secondary small mt-2 mb-0">Database juga ter-seed dengan ~7.500 record demo: 3.505 tickets, 1.805 replies, 150 conversations + 1.500 messages, 156 knowledge articles, 60 blog posts, 45 services.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title fs-3">Cara Jalan (Local Install)</h2>
            <ol class="mb-2">
                <li>Clone repo dan masuk ke folder project.</li>
                <li>Copy <code>.env.example</code> ke <code>.env</code>, isi <code>DB_*</code> dengan kredensial MySQL lokal.</li>
                <li>Jalankan perintah berikut:</li>
            </ol>
            <pre class="bg-dark text-white rounded p-3 small mb-0" style="overflow-x: auto;"><code>composer install
npm install &amp;&amp; npm run build
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoDataSeeder   # optional: 5k+ demo records
php artisan storage:link
php artisan serve                            # http://localhost:8000
# untuk realtime chat (opsional):
php artisan reverb:start
php artisan queue:work</code></pre>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title fs-3">Fitur Utama</h2>
            <div class="row row-cards">
                @foreach([
                    ['Ticket Management', 'Multi-channel (web, email piping, API), SLA, prioritas, kategori, departement, bulk action, star.'],
                    ['AI Auto-Reply & Classification', 'Pluggable provider (OpenAI, Anthropic, Gemini, Ollama, dll.) — BYOK, user input API key sendiri.'],
                    ['Knowledge Base + FAQ', 'Public knowledge base + search, FAQ, kategori, view counter.'],
                    ['Conversations (Live Chat)', 'Realtime via Laravel Reverb, agent assignment, internal note.'],
                    ['Canned Responses & Automation', 'Template balasan, rules engine untuk auto-assign, auto-tag, auto-reply.'],
                    ['SLA & Analytics', 'Policy per kategori/prioritas, first-response, resolution time, charts.'],
                    ['Email Templates', 'Notification email yang fully editable di admin panel.'],
                    ['Programmatic SEO', 'Halaman /best-helpdesk-software, /compare/{a}-vs-{b}, /alternatives-to/{slug}, sitemap.xml dinamis.'],
                    ['API Keys + Sanctum', 'REST API + Bearer token, rate-limit, scope per key.'],
                    ['Activity Log', 'Audit trail seluruh aksi admin dan agent.'],
                    ['Multi-role RBAC', 'Spatie permission: admin / agent / customer + permission granular.'],
                    ['Dynamic AI Providers', '20 preset JSON, 3 format adapter (OpenAI-compatible / Anthropic / Gemini) — provider baru cukup add di UI.'],
                ] as $f)
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-body py-2">
                                <h3 class="card-title mb-1">{{ $f[0] }}</h3>
                                <p class="text-secondary small mb-0">{{ $f[1] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title fs-3">Tutorial Singkat</h2>

            <ol class="text-secondary">
                <li>
                    <strong>Login sebagai Admin</strong> — buka <code>/login</code>, gunakan kredensial <code>admin@helpdesk.test</code> / <code>password</code>.
                </li>
                <li>
                    <strong>Add AI Provider</strong> — di <code>/admin/ai-providers/create</code>, isi nama provider (bebas), pilih format (OpenAI-compatible / Anthropic / Gemini), masukkan API key &amp; base URL. Klik &quot;Test Connection&quot; untuk validasi.
                </li>
                <li>
                    <strong>Mapping AI Feature</strong> — di <code>/admin/ai-features</code>, pilih provider + model untuk tiap fitur (auto-classify, smart reply, sentiment, dll.).
                </li>
                <li>
                    <strong>Buat Departemen &amp; Kategori</strong> — <code>/admin/departments</code> dan <code>/admin/categories</code>.
                </li>
                <li>
                    <strong>Tambah Tim Agent</strong> — <code>/admin/users</code> &rarr; New User &rarr; assign role &quot;agent&quot;.
                </li>
                <li>
                    <strong>Test Submit Ticket sebagai Customer</strong> — logout, login pakai <code>customer@helpdesk.test</code>, buka <code>/tickets/create</code>.
                </li>
                <li>
                    <strong>Reply &amp; Assign</strong> — dari admin panel, buka ticket, gunakan form reply, tombol assign, update status/priority.
                </li>
                <li>
                    <strong>Public-facing Pages</strong> — <code>/blog</code>, <code>/services</code>, <code>/knowledge-base</code>, <code>/contact</code>, dan halaman pSEO seperti <code>/best-helpdesk-software/{{ date('Y') }}</code>.
                </li>
            </ol>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title fs-3">REST API (Sanctum Bearer)</h2>
            <p class="text-secondary small mb-2">Login lewat <code>POST /api/login</code> untuk dapat token. Pasang di header <code>Authorization: Bearer &lt;token&gt;</code>.</p>
            <div class="table-responsive">
                <table class="table table-vcenter card-table small font-monospace">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Fungsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['POST', '/api/login', 'Login → dapat Bearer token'],
                            ['GET',  '/api/me', 'Profil user aktif'],
                            ['GET',  '/api/tickets', 'List ticket milik user'],
                            ['POST', '/api/tickets', 'Buat ticket baru'],
                            ['GET',  '/api/tickets/{id}', 'Detail ticket'],
                            ['GET',  '/api/knowledge', 'List artikel KB'],
                            ['GET',  '/api/knowledge/search?q=', 'Cari artikel'],
                            ['GET',  '/api/conversations', 'List chat conversation'],
                            ['POST', '/api/conversations/{id}/message', 'Kirim pesan chat'],
                            ['POST', '/api/ai/classify', 'Klasifikasi subject+body'],
                            ['POST', '/api/ai/suggest', 'Generate draft reply'],
                            ['POST', '/api/ai/sentiment', 'Analisa sentimen pesan'],
                            ['GET',  '/api/analytics/summary', 'Ringkasan analytics'],
                        ] as $row)
                            <tr>
                                <td><strong class="{{ $row[0] === 'GET' ? 'text-success' : 'text-primary' }}">{{ $row[0] }}</strong></td>
                                <td>{{ $row[1] }}</td>
                                <td class="text-secondary">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <pre class="bg-dark text-white rounded p-3 small mb-0" style="overflow-x: auto;"><code>curl -X POST {{ url('/api/login') }} \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@helpdesk.test","password":"password"}'

# response: { "token": "1|abc...", "user": {...} }

curl {{ url('/api/tickets') }} \
  -H "Authorization: Bearer 1|abc..."</code></pre>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title fs-3">Quick Links</h2>
            <div class="row g-2">
                <div class="col-6 col-md-3"><a class="btn w-100" href="/admin">Admin Dashboard</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/dashboard">User Dashboard</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/blog">Blog</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/services">Services</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/knowledge-base">Knowledge Base</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/contact">Contact</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/sitemap.xml">sitemap.xml</a></div>
                <div class="col-6 col-md-3"><a class="btn w-100" href="/best-helpdesk-software">Best Helpdesk</a></div>
            </div>
            <p class="text-secondary small mt-2 mb-0">Dokumentasi teknis lengkap (PRD, arsitektur, ERD, security, deployment) ada di folder <code>docs/</code> repository — 15 file Markdown ~6.200 baris.</p>
        </div>
    </div>
</x-app-layout>
