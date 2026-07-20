@extends('layouts.app')

@section('title', 'News & Notifications')

@push('styles')
<style>
    :root {
        --g:      #198754;
        --g-dk:   #14532d;
        --g-lt:   #dcfce7;
        --g-mid:  #86efac;
        --red:    #dc2626;
        --amber:  #d97706;
        --bg:     #f8faf8;
        --surf:   #ffffff;
        --border: #d4e8d4;
        --text:   #1a2e1a;
        --muted:  #64748b;
        --rad:    10px;
        --sh:     0 1px 8px rgba(22,163,74,.10);
    }

    .nw-page  { font-family: 'Outfit', sans-serif; background: var(--bg); min-height: 100vh; padding: 2rem; color: var(--text); }
    .nw-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; }
    .nw-header h1 { font-weight: 800; font-size: 1.9rem; margin: 0; }
    .nw-header h1 span { color: var(--g); }
    .nw-header p { margin: .2rem 0 0; font-size: .82rem; color: var(--muted); }

    .nw-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 8px; font-size: .82rem; font-weight: 600; border: 1.5px solid transparent; cursor: pointer; transition: .15s; text-decoration: none; white-space: nowrap; }
    .nw-btn--green { background: var(--g); color: #fff; border-color: var(--g); }
    .nw-btn--green:hover { background: var(--g-dk); color: #fff; }
    .nw-btn--ghost { background: transparent; color: var(--g); border-color: var(--g); }
    .nw-btn--ghost:hover { background: var(--g-lt); }
    .nw-btn--dark  { background: var(--text); color: #fff; }
    .nw-btn--dark:hover { opacity: .88; color: #fff; }
    .nw-btn--sm { padding: .28rem .65rem; font-size: .75rem; }
    .nw-btn--red { background: var(--red); color: #fff; border-color: var(--red); }
    .nw-btn--red:hover { background: #b91c1c; }

    .nw-card { background: var(--surf); border: 1.5px solid var(--border); border-radius: var(--rad); box-shadow: var(--sh); overflow: hidden; margin-bottom: 1.25rem; }
    .nw-card__head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; background: var(--g-lt); border-bottom: 1.5px solid var(--border); }
    .nw-card__head-title { font-weight: 700; font-size: 1rem; color: var(--g-dk); margin: 0; }

    .nw-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .nw-table thead th { background: var(--g-lt); color: var(--g-dk); font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; padding: .75rem 1rem; border-bottom: 2px solid var(--g-mid); white-space: nowrap; }
    .nw-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .nw-table tbody tr:hover td { background: #f0faf0; }
    .nw-table tbody tr:last-child td { border-bottom: none; }

    .nw-badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .03em; white-space: nowrap; }
    .nw-badge--zimra   { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .nw-badge--system  { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
    .nw-badge--general { background: var(--g-lt); color: var(--g-dk); border: 1px solid var(--g-mid); }
    .nw-badge--pub  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .nw-badge--draft { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

    .nw-body-preview { max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--muted); font-size: .82rem; }

    #nw-toasts { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: .5rem; }
    .nw-toast { padding: .65rem 1rem; border-radius: 8px; font-size: .82rem; font-weight: 500; display: flex; align-items: center; gap: .5rem; min-width: 220px; box-shadow: var(--sh); animation: toastIn .22s ease; }
    @keyframes toastIn { from { transform: translateX(30px); opacity: 0; } to { transform: none; opacity: 1; } }
    .nw-toast.success { background: var(--g); color: #fff; }
    .nw-toast.error   { background: var(--red); color: #fff; }
</style>
@endpush

@section('content')
<div class="nw-page">

    <div class="nw-header">
        <div>
            <h1>News &amp; <span>Notifications</span></h1>
            <p>Manage ZIMRA updates, system announcements, and general notices</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('news.create') }}" class="nw-btn nw-btn--green">
                <i class="bi bi-plus-lg"></i>New Post
            </a>
            <a href="{{ route('dashboard') }}" class="nw-btn nw-btn--dark">
                <i class="bi bi-arrow-left-short fs-5"></i>Dashboard
            </a>
        </div>
    </div>

    <div class="nw-card">
        <div class="nw-card__head">
            <h2 class="nw-card__head-title"><i class="bi bi-newspaper me-2"></i>All Posts</h2>
            <span style="font-size:.78rem;color:var(--muted)">{{ $posts->total() }} post(s)</span>
        </div>
        <div style="overflow-x:auto">
            <table class="nw-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Preview</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Author</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $i => $post)
                    <tr>
                        <td style="color:var(--muted);font-size:.75rem;font-family:monospace">{{ $posts->firstItem() + $i }}</td>
                        <td style="font-weight:600;max-width:220px">{{ $post->title }}</td>
                        <td><div class="nw-body-preview">{{ $post->body }}</div></td>
                        <td>
                            @php
                                $catKey = match($post->category) {
                                    'ZIMRA Update' => 'zimra',
                                    'System'       => 'system',
                                    default        => 'general',
                                };
                            @endphp
                            <span class="nw-badge nw-badge--{{ $catKey }}">{{ $post->category }}</span>
                        </td>
                        <td>
                            <button class="nw-badge {{ $post->is_published ? 'nw-badge--pub' : 'nw-badge--draft' }} toggle-btn"
                                    style="cursor:pointer;border:none;background:inherit"
                                    data-id="{{ $post->id }}"
                                    title="Click to toggle">
                                {{ $post->is_published ? 'Published' : 'Draft' }}
                            </button>
                        </td>
                        <td style="font-size:.78rem;color:var(--muted);white-space:nowrap">
                            {{ $post->published_at ? $post->published_at->format('d M Y H:i') : '—' }}
                        </td>
                        <td style="font-size:.78rem">{{ $post->author?->name ?? '—' }}</td>
                        <td>
                            <div style="display:flex;gap:.35rem;justify-content:center">
                                <a href="{{ route('news.edit', $post->id) }}" class="nw-btn nw-btn--ghost nw-btn--sm">
                                    <i class="bi bi-pencil"></i>Edit
                                </a>
                                <button class="nw-btn nw-btn--red nw-btn--sm delete-btn"
                                        data-id="{{ $post->id }}"
                                        data-title="{{ $post->title }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:3rem;color:var(--muted)">
                            <i class="bi bi-newspaper" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                            No posts yet. <a href="{{ route('news.create') }}" style="color:var(--g)">Create one.</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;padding:.85rem 1.25rem;border-top:1.5px solid var(--border);font-size:.8rem;color:var(--muted);background:var(--bg)">
            <span>Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }}</span>
            {{ $posts->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

<div id="nw-toasts"></div>

{{-- Delete confirm modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header border-0" style="background:#fee2e2">
                <h5 class="modal-title fw-bold" style="color:#991b1b"><i class="bi bi-trash me-2"></i>Delete Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.88rem;padding:1.25rem">
                Are you sure you want to delete <strong id="deleteTitle"></strong>? This cannot be undone.
            </div>
            <div class="modal-footer border-0" style="gap:.5rem;padding:.85rem 1.25rem">
                <button type="button" class="nw-btn nw-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="nw-btn nw-btn--red"><i class="bi bi-trash"></i>Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    function toast(msg, type = 'success') {
        const wrap = document.getElementById('nw-toasts');
        const el   = document.createElement('div');
        el.className = `nw-toast ${type}`;
        el.innerHTML = `<i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i>${msg}`;
        wrap.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = '.3s'; setTimeout(() => el.remove(), 320); }, 3200);
    }

    @if(session('success')) toast('{{ session('success') }}', 'success'); @endif
    @if(session('error'))   toast('{{ session('error') }}',   'error');   @endif

    // ── Toggle publish ──
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch(`/news/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const pub = data.is_published;
                    this.textContent = pub ? 'Published' : 'Draft';
                    this.className   = `nw-badge ${pub ? 'nw-badge--pub' : 'nw-badge--draft'} toggle-btn`;
                    this.style.cursor = 'pointer';
                    this.style.border = 'none';
                    this.style.background = 'inherit';
                    toast(pub ? 'Post published.' : 'Post moved to draft.', 'success');
                }
            })
            .catch(() => toast('Failed to toggle status.', 'error'));
        });
    });

    // ── Delete ──
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('deleteTitle').textContent = this.dataset.title;
            document.getElementById('deleteForm').action = `/news/${this.dataset.id}`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
});
</script>
@endpush
