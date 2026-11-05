{{-- Shared Rate & Review Product modal (used by customer dashboard + order detail).
     Open via JS: openReviewModal(orderItemId, productName, imageUrl, orderNumber) --}}

@if(session('review_success'))
    <div style="background:var(--success-soft); border:1.5px solid var(--success-border); border-radius:12px; padding:0.85rem 1.1rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.6rem; color:var(--text-success); font-size:0.88rem; font-weight:600;">
        <i class="fa-solid fa-circle-check" style="color:var(--text-success); font-size:1.05rem;"></i>
        {{ session('review_success') }}
    </div>
@endif
@if(session('review_error'))
    <div style="background:var(--danger-soft); border:1.5px solid var(--danger-border); border-radius:12px; padding:0.85rem 1.1rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.6rem; color:var(--text-danger); font-size:0.88rem; font-weight:600;">
        <i class="fa-solid fa-circle-exclamation" style="color:var(--text-danger); font-size:1.05rem;"></i>
        {{ session('review_error') }}
    </div>
@endif

<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.7); z-index:99999; align-items:center; justify-content:center; padding:1.5rem 1rem; backdrop-filter:blur(4px);">
    <div style="background:var(--bg-surface); border-radius:16px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.3); border:1px solid var(--border-color); overflow:hidden; max-height:90vh; overflow-y:auto;">
        <!-- Modal Header -->
        <div style="background:var(--warning-soft); border-bottom:1px solid var(--warning-border); padding:1.25rem 1.5rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:var(--warning-soft); color:var(--text-warning); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <h3 style="font-size:1.1rem; font-weight:800; color:var(--text-warning); margin:0;">Rate &amp; Review Product</h3>
                    <div style="font-size:0.75rem; color:var(--text-warning);">Only for completed orders</div>
                </div>
            </div>
            <button type="button" onclick="closeReviewModal()" style="background:none; border:none; color:var(--text-warning); font-size:1.2rem; cursor:pointer; padding:0.25rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('customer.reviews.store') }}" method="POST" enctype="multipart/form-data" style="padding:1.5rem;" onsubmit="return validateReviewForm(this)">
            @csrf
            <input type="hidden" name="order_item_id" id="review_order_item_id" value="">

            <!-- Product Picture + Title + Order Reference -->
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.1rem; background:var(--bg-page); border:1px solid var(--border-color); border-radius:12px; padding:0.65rem 0.85rem;">
                <div style="width:48px; height:48px; border-radius:10px; overflow:hidden; background:var(--accent-soft); border:1px solid var(--info-border); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <img id="review_product_image" src="" alt="" style="display:none; width:100%; height:100%; object-fit:cover;">
                    <i id="review_product_icon" class="fa-solid fa-print" style="color:var(--text-accent); font-size:1.1rem;"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.72rem; color:var(--text-muted);">Reviewing</div>
                    <strong id="review_product_name" style="color:var(--text-main); font-size:0.92rem; overflow-wrap:break-word;"></strong>
                </div>
                <span id="review_order_label" style="margin-left:auto; font-size:0.7rem; font-weight:800; color:var(--text-accent); background:var(--accent-soft); padding:0.15rem 0.5rem; border-radius:6px; white-space:nowrap; flex-shrink:0;"></span>
            </div>

            <!-- Star Rating Picker -->
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.85rem; font-weight:700; color:var(--text-main); display:block; margin-bottom:0.4rem;">Your Rating <span style="color:var(--text-danger);">*</span></label>
                <div id="reviewStarPicker" style="display:flex; gap:0.5rem; font-size:1.7rem; cursor:pointer;">
                    @for($s = 1; $s <= 5; $s++)
                        <i class="fa-solid fa-star review-star-icon" data-star="{{ $s }}" onclick="setReviewStars({{ $s }})" style="color:var(--text-disabled); transition:color 0.15s, transform 0.15s;" aria-label="{{ $s }} star{{ $s > 1 ? 's' : '' }}"></i>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="review_rating_input" value="">
                <div id="review_rating_label" style="font-size:0.78rem; color:var(--text-muted); margin-top:0.35rem;">Tap a star to rate (1 = Poor, 5 = Excellent)</div>
                <div id="review_rating_error" style="font-size:0.78rem; color:var(--text-danger); font-weight:600; margin-top:0.35rem; display:none;"><i class="fa-solid fa-circle-exclamation"></i> Please select a star rating.</div>
            </div>

            <!-- Comment -->
            <div style="margin-bottom:1rem;">
                <label for="review_comment" style="font-size:0.85rem; font-weight:700; color:var(--text-main); display:block; margin-bottom:0.4rem;">Your Review <span style="font-weight:500; color:var(--text-muted);">(optional)</span></label>
                <textarea name="comment" id="review_comment" rows="4" maxlength="1000" placeholder="How was the print quality, turnaround time, and overall experience?" style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid var(--border-color); border-radius:10px; font-size:0.88rem; font-family:inherit; outline:none; resize:vertical; background:var(--bg-page); box-sizing:border-box;" onfocus="this.style.background='#FFF'; this.style.borderColor='#F97316';" onblur="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1';"></textarea>
            </div>

            <!-- Optional Photo -->
            <div style="margin-bottom:1.25rem;">
                <label for="review_photo" style="font-size:0.85rem; font-weight:700; color:var(--text-main); display:block; margin-bottom:0.4rem;">Upload Photo <span style="font-weight:500; color:var(--text-muted);">(optional, max 2 MB)</span></label>
                <input type="file" name="photo" id="review_photo" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewReviewPhoto(this)" style="font-size:0.82rem; width:100%;">
                <img id="review_photo_preview" src="" alt="Review photo preview" style="display:none; max-height:140px; border-radius:10px; border:1.5px solid var(--border-color); margin-top:0.6rem;">
                <div id="review_photo_error" style="font-size:0.78rem; color:var(--text-danger); font-weight:600; margin-top:0.35rem; display:none;"></div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" onclick="closeReviewModal()" class="btn btn-outline" style="padding:0.55rem 1.1rem; font-size:0.88rem;">
                    Cancel
                </button>
                <button type="submit" class="btn" style="background:var(--warning-color); color:var(--text-inverse); font-weight:700; padding:0.55rem 1.25rem; font-size:0.88rem; border-radius:8px;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Review
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Rate & Review Product Modal (shared) ──────────────
const REVIEW_STAR_LABELS = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

function openReviewModal(orderItemId, productName, imageUrl, orderNumber) {
    const modal = document.getElementById('reviewModal');
    if (!modal) return;
    document.getElementById('review_order_item_id').value = orderItemId;
    document.getElementById('review_product_name').textContent = productName || '';

    const img = document.getElementById('review_product_image');
    const icon = document.getElementById('review_product_icon');
    if (img && icon) {
        if (imageUrl) {
            img.src = imageUrl;
            img.style.display = 'block';
            icon.style.display = 'none';
        } else {
            img.src = '';
            img.style.display = 'none';
            icon.style.display = 'block';
        }
    }

    const orderLabel = document.getElementById('review_order_label');
    if (orderLabel) orderLabel.textContent = orderNumber ? 'Order #' + orderNumber : '';

    resetReviewForm();
    modal.style.display = 'flex';
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function setReviewStars(rating) {
    document.querySelectorAll('.review-star-icon').forEach(star => {
        const s = parseInt(star.getAttribute('data-star'));
        star.style.color = s <= rating ? '#F59E0B' : '#CBD5E1';
        star.style.transform = s <= rating ? 'scale(1.1)' : 'scale(1)';
    });
    const input = document.getElementById('review_rating_input');
    if (input) input.value = rating;
    const label = document.getElementById('review_rating_label');
    if (label) label.textContent = rating > 0 ? rating + '/5 — ' + REVIEW_STAR_LABELS[rating - 1] : 'Tap a star to rate (1 = Poor, 5 = Excellent)';
    const err = document.getElementById('review_rating_error');
    if (err) err.style.display = 'none';
}

function resetReviewForm() {
    setReviewStars(0);
    document.getElementById('review_rating_input').value = '';
    const label = document.getElementById('review_rating_label');
    if (label) label.textContent = 'Tap a star to rate (1 = Poor, 5 = Excellent)';
    const comment = document.getElementById('review_comment');
    if (comment) comment.value = '';
    const photo = document.getElementById('review_photo');
    if (photo) photo.value = '';
    const preview = document.getElementById('review_photo_preview');
    if (preview) preview.style.display = 'none';
    const photoErr = document.getElementById('review_photo_error');
    if (photoErr) photoErr.style.display = 'none';
    // Render stars as inactive for a 0 rating
    document.querySelectorAll('.review-star-icon').forEach(star => {
        star.style.color = '#CBD5E1';
        star.style.transform = 'scale(1)';
    });
}

function previewReviewPhoto(input) {
    const preview = document.getElementById('review_photo_preview');
    const errBox = document.getElementById('review_photo_error');
    if (errBox) errBox.style.display = 'none';
    if (!input.files || !input.files.length || !preview) return;
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
        if (errBox) {
            errBox.textContent = 'Photo must not exceed 2 MB. Please choose a smaller image.';
            errBox.style.display = 'block';
        }
        input.value = '';
        preview.style.display = 'none';
        return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function validateReviewForm(form) {
    const rating = document.getElementById('review_rating_input').value;
    if (!rating || parseInt(rating) < 1) {
        const err = document.getElementById('review_rating_error');
        if (err) err.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    const photo = document.getElementById('review_photo');
    if (photo && photo.files.length && photo.files[0].size > 2 * 1024 * 1024) {
        return false;
    }
    return true;
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeReviewModal();
});
</script>
