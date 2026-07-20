@extends('layouts.app')

@section('title', isset($post) ? 'Edit Post' : 'New Post')

@push('styles')
<style>
    :root {
        --g: #198754; --g-dk: #14532d; --g-lt: #dcfce7; --g-mid: #86efac;
        --bg: #f8faf8; --surf: #ffffff; --border: #d4e8d4;
        --text: #1a2e1a; --muted: #64748b; --red: #dc2626;
        --rad: 10px; --sh: 0 1px 8px rgba(22,163,74,.10);
    }

    .nw-page  { font-family: 'Outfit', sans-serif; background: var(--bg); min-height: 100vh; padding: 2rem; color: var(--text); }
    .nw-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; }
    .nw-header h1 { font-weight: 800; font-size: 1.9rem; margin: 0; }
    .nw-header h1 span { color: var(--g); }

    .nw-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 8px; font-size: .82rem; font-weight: 600; border: 1.5px solid transparent; cursor: pointer; transition: .15s; text-decoration: none; white-space: nowrap; }
    .nw-btn--green { background: var(--g); color: #fff; border-color: var(--g); }
    .nw-btn--green:hover { background: var(--g-dk); color: #fff; }
    .nw-btn--ghost { background: transparent; color: var(--g); border-color: var(--g); }
    .nw-btn--ghost:hover { background: var(--g-lt); }
    .nw-btn--dark { background: var(--text); color: #fff; }
    .nw-btn--dark:hover { opacity: .88; color: #fff; }

    .nw-card { background: var(--surf); border: 1.5px solid var(--border); border-radius: var(--rad); box-shadow: var(--sh); overflow: hidden; max-width: 860px; }
    .nw-card__head { padding: 1rem 1.5rem; background: var(--g-lt); border-bottom: 1.5px solid var(--border); font-weight: 700; font-size: 1rem; color: var(--g-dk); }
    .nw-card__body { padding: 1.5rem; }

    .nw-label { display: block; font-size: .78rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: .3rem; }
    .nw-input { width: 100%; padding: .55rem .75rem; border: 1.5px solid var(--border); border-radius: 8px; font-size: .88rem; color: var(--text); background: var(--bg); outline: none; transition: border-color .15s; font-family: inherit; }
    .nw-input:focus { border-color: var(--g); background: #fff; }
    .nw-input.is-invalid { border-color: var(--red); }
    .nw-err { font-size: .75rem; color: var(--red); margin-top: .25rem; }

    .nw-textarea { resize: vertical; min-height: 320px; line-height: 1.6; }

    .nw-check-row { display: flex; align-items: center; gap: .6rem; padding: .75rem 1rem; background: var(--g-lt); border: 1.5px solid var(--g-mid); border-radius: 8px; }
    .nw-check-row input[type=checkbox] { width: 18px; height: 18px; accent-color: var(--g); cursor: pointer; }
    .nw-check-row label { font-size: .88rem; font-weight: 600; color: var(--g-dk); cursor: pointer; margin: 0; }
    .nw-check-row small { font-size: .75rem; color: var(--muted); margin-left: .25rem; }

    .cat-badges { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .5rem; }
    .cat-opt { padding: .3rem .85rem; border-radius: 999px; font-size: .78rem; font-weight: 600; cursor: pointer; border: 2px solid transparent; transition: .15s; }
    .cat-opt[data-val="ZIMRA Update"]  { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .cat-opt[data-val="System"]        { background: #fef9c3; color: #854d0e; border-color: #fde047; }
    .cat-opt[data-val="General"]       { background: var(--g-lt); color: var(--g-dk); border-color: var(--g-mid); }
    .cat-opt.selected { box-shadow: 0 0 0 3px rgba(22,163,74,.35); transform: scale(1.05); }
</style>
@endpush

@section('content')
<div class="nw-page">

    <div class="nw-header">
        <div>
            <h1>{{ isset($post) ? 'Edit' : 'New' }} <span>Post</span></h1>
            <p>{{ isset($post) ? 'Update the post content and settings' : 'Compose a new news post or notification' }}</p>
        </div>
        <a href="{{ route('news.index') }}" class="nw-btn nw-btn--dark">
            <i class="bi bi-arrow-left-short fs-5"></i>Back
        </a>
    </div>

    <div class="nw-card">
        <div class="nw-card__head">
            <i class="bi bi-pencil-square me-2"></i>{{ isset($post) ? 'Edit Post' : 'Create Post' }}
        </div>
        <div class="nw-card__body">
            <form method="POST"
                  action="{{ isset($post) ? route('news.update', $post->id) : route('news.store') }}">
                @csrf
                @if(isset($post)) @method('PUT') @endif

                <div class="row g-3">

                    {{-- Title --}}
                    <div class="col-12">
                        <label class="nw-label" for="title">Title *</label>
                        <input type="text" id="title" name="title" class="nw-input @error('title') is-invalid @enderror"
                               value="{{ old('title', $post->title ?? '') }}"
                               placeholder="Post headline…" required>
                        @error('title')<p class="nw-err">{{ $message }}</p>@enderror
                    </div>

                    {{-- Category --}}
                    <div class="col-12">
                        <label class="nw-label">Category *</label>
                        <div class="cat-badges">
                            @foreach(['ZIMRA Update', 'System', 'General'] as $cat)
                            <span class="cat-opt {{ old('category', $post->category ?? 'General') === $cat ? 'selected' : '' }}"
                                  data-val="{{ $cat }}"
                                  onclick="selectCat(this)">{{ $cat }}</span>
                            @endforeach
                        </div>
                        <input type="hidden" name="category" id="categoryInput"
                               value="{{ old('category', $post->category ?? 'General') }}">
                        @error('category')<p class="nw-err">{{ $message }}</p>@enderror
                    </div>

                    {{-- Body --}}
                    <div class="col-12">
                        <label class="nw-label" for="body">Body *</label>
                        <textarea id="body" name="body"
                                  class="nw-input nw-textarea @error('body') is-invalid @enderror"
                                  placeholder="Write your post content here…" required>{{ old('body', $post->body ?? '') }}</textarea>
                        @error('body')<p class="nw-err">{{ $message }}</p>@enderror
                    </div>

                    {{-- Publish toggle --}}
                    <div class="col-12">
                        <div class="nw-check-row">
                            <input type="checkbox" id="is_published" name="is_published" value="1"
                                   {{ old('is_published', $post->is_published ?? false) ? 'checked' : '' }}>
                            <label for="is_published">Publish immediately
                                <small>(unchecked = saved as draft)</small>
                            </label>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-12 d-flex gap-2 justify-content-end pt-1">
                        <a href="{{ route('news.index') }}" class="nw-btn nw-btn--ghost">Cancel</a>
                        <button type="submit" class="nw-btn nw-btn--green">
                            <i class="bi bi-save2"></i>{{ isset($post) ? 'Save Changes' : 'Create Post' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function selectCat(el) {
    document.querySelectorAll('.cat-opt').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('categoryInput').value = el.dataset.val;
}
</script>
@endpush
