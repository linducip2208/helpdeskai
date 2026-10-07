<div x-data="commandPalette()" x-init="init()" x-cloak>
    <div x-show="open" x-transition.opacity class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" aria-label="{{ __('Command palette') }}" @keydown.escape.window="close()">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <input type="text" x-model="q" @input="search()" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()" class="form-control form-control-lg" placeholder="{{ __('Type a command or search…') }}" aria-label="{{ __('Command palette search') }}">
                    <div class="list-group list-group-flush mt-2" style="max-height: 20rem; overflow-y: auto;">
                        <template x-for="(item, i) in items" :key="i">
                            <a :href="item.url" class="list-group-item list-group-item-action" :class="{ 'active': i === active }">
                                <span x-text="item.label"></span>
                                <small class="text-muted ms-2" x-text="item.hint"></small>
                            </a>
                        </template>
                        <div x-show="items.length === 0" class="list-group-item text-muted">{{ __('No matches. Try ticket UID, customer name, or action.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function commandPalette() {
    return {
        open: false,
        q: '',
        items: [],
        active: 0,
        timer: null,
        init() {
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    this.open ? this.close() : this.openPalette();
                }
            });
        },
        openPalette() { this.open = true; this.q = ''; this.items = this.commands(); this.active = 0; setTimeout(() => document.querySelector('[x-data] input'), 0); },
        close() { this.open = false; },
        commands() {
            return [
                { label: 'New ticket', hint: 'create', url: '{{ route('admin.tickets.create') }}' },
                { label: 'My work', hint: 'queue', url: '{{ route('admin.my-work.index') }}' },
                { label: 'Support inbox', hint: 'queue', url: '{{ route('admin.support.inbox') }}' },
                { label: 'Knowledge base', hint: 'docs', url: '{{ route('admin.knowledge.index') }}' },
                { label: 'Reports / analytics', hint: 'stats', url: '{{ route('admin.analytics.index') }}' },
                { label: 'Settings', hint: 'config', url: '{{ route('admin.settings.index') }}' },
            ];
        },
        search() {
            const q = this.q.trim();
            const base = this.commands().filter(c => (c.label + ' ' + c.hint).toLowerCase().includes(q.toLowerCase()));
            if (q.length >= 2) {
                base.unshift({ label: 'Search for "' + q + '"', hint: 'tickets · customers · articles', url: '{{ route('admin.search.index') }}?q=' + encodeURIComponent(q) });
            }
            this.items = base;
            this.active = 0;
        },
        move(d) { this.active = (this.active + d + this.items.length) % Math.max(1, this.items.length); },
        go() { if (this.items[this.active]) window.location.href = this.items[this.active].url; },
    }
}
</script>
