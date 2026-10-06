<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Documentation &amp; Tutorial
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">HelpDesk AI Docs</h1>
                <p class="text-gray-500 dark:text-gray-400">Quick start, fitur lengkap, dan kredensial akses demo. Cocok untuk evaluasi maupun saat onboarding tim.</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-amber-900 mb-3">Akses Demo</h2>
                <p class="text-sm text-amber-800 mb-3">Semua akun di-seed otomatis. Password sama untuk semua: <code class="font-mono font-bold">password</code></p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-amber-300">
                            <tr class="text-left text-amber-800">
                                <th class="py-2 pr-4">Role</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Password</th>
                                <th class="py-2 pr-4">Akses</th>
                            </tr>
                        </thead>
                        <tbody class="text-amber-900">
                            <tr class="border-b border-amber-200">
                                <td class="py-2 pr-4 font-medium">Admin</td>
                                <td class="py-2 pr-4 font-mono">admin@helpdesk.test</td>
                                <td class="py-2 pr-4 font-mono">password</td>
                                <td class="py-2 pr-4">Full admin panel di <code>/admin</code></td>
                            </tr>
                            <tr class="border-b border-amber-200">
                                <td class="py-2 pr-4 font-medium">Agent</td>
                                <td class="py-2 pr-4 font-mono">agent@helpdesk.test</td>
                                <td class="py-2 pr-4 font-mono">password</td>
                                <td class="py-2 pr-4">Tickets, conversations, knowledge base</td>
                            </tr>
                            <tr class="border-b border-amber-200">
                                <td class="py-2 pr-4 font-medium">Customer</td>
                                <td class="py-2 pr-4 font-mono">customer@helpdesk.test</td>
                                <td class="py-2 pr-4 font-mono">password</td>
                                <td class="py-2 pr-4">Dashboard user, submit ticket</td>
                            </tr>
                            <tr class="border-b border-amber-200">
                                <td class="py-2 pr-4 font-medium">Bulk Customer</td>
                                <td class="py-2 pr-4 font-mono">customer0@demo.test &hellip; customer299@demo.test</td>
                                <td class="py-2 pr-4 font-mono">password</td>
                                <td class="py-2 pr-4">300 akun customer untuk uji pagination &amp; load</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-medium">Bulk Agent</td>
                                <td class="py-2 pr-4 font-mono">agent0@demo.test &hellip; agent39@demo.test</td>
                                <td class="py-2 pr-4 font-mono">password</td>
                                <td class="py-2 pr-4">40 akun agent untuk uji assignment &amp; workload</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-amber-700 mt-3">Database juga ter-seed dengan ~7.500 record demo: 3.505 tickets, 1.805 replies, 150 conversations + 1.500 messages, 156 knowledge articles, 60 blog posts, 45 services.</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Cara Jalan (Local Install)</h2>
                <ol class="space-y-3 list-decimal list-inside text-sm text-gray-700 dark:text-gray-300">
                    <li>Clone repo dan masuk ke folder project.</li>
                    <li>Copy <code>.env.example</code> ke <code>.env</code>, isi <code>DB_*</code> dengan kredensial MySQL lokal.</li>
                    <li>Jalankan perintah berikut:</li>
                </ol>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 mt-3 overflow-x-auto text-xs"><code>composer install
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Fitur Utama</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                        <div class="border border-gray-100 dark:border-gray-700 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $f[0] }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $f[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Tutorial Singkat</h2>

                <ol class="space-y-5 list-decimal list-inside text-gray-700 dark:text-gray-300">
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

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">REST API (Sanctum Bearer)</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Login lewat <code>POST /api/login</code> untuk dapat token. Pasang di header <code>Authorization: Bearer &lt;token&gt;</code>.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs font-mono">
                        <thead class="border-b border-gray-200 dark:border-gray-700 text-gray-500">
                            <tr class="text-left">
                                <th class="py-2 pr-4">Method</th>
                                <th class="py-2 pr-4">Endpoint</th>
                                <th class="py-2 pr-4">Fungsi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 dark:text-gray-300">
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
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-1.5 pr-4 font-bold {{ $row[0] === 'GET' ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $row[0] }}</td>
                                    <td class="py-1.5 pr-4">{{ $row[1] }}</td>
                                    <td class="py-1.5 pr-4 font-sans text-gray-600 dark:text-gray-400">{{ $row[2] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 mt-4 overflow-x-auto text-xs"><code>curl -X POST {{ url('/api/login') }} \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@helpdesk.test","password":"password"}'

# response: { "token": "1|abc...", "user": {...} }

curl {{ url('/api/tickets') }} \
  -H "Authorization: Bearer 1|abc..."</code></pre>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Quick Links</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/admin">Admin Dashboard</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/dashboard">User Dashboard</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/blog">Blog</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/services">Services</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/knowledge-base">Knowledge Base</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/contact">Contact</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/sitemap.xml">sitemap.xml</a>
                    <a class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-center" href="/best-helpdesk-software">Best Helpdesk</a>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">Dokumentasi teknis lengkap (PRD, arsitektur, ERD, security, deployment) ada di folder <code>docs/</code> repository — 15 file Markdown ~6.200 baris.</p>
            </div>

        </div>
    </div>
</x-app-layout>
