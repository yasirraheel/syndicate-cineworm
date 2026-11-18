<div class="row">
    <div class="col-12 col-md-6 form-group">
        <label for="label">Label</label>
        <input class="input-syndicate" id="label" name="label" value="{{ old('label', $link->label) }}" required>
    </div>
    <div class="col-12 col-md-6 form-group">
        <label for="url">URL</label>
        <input class="input-syndicate" id="url" name="url" value="{{ old('url', $link->url) }}" required>
    </div>
    <div class="col-12 col-md-6 form-group">
        <label for="sort_order">Sort order</label>
        <input class="input-syndicate" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $link->sort_order ?? 0) }}">
    </div>
    <div class="col-12 col-md-6 form-group">
        <div class="filter-toggle justify-content-start">
            <div class="filter-toggle-wrapper">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="checkbox-syndicate" @checked(old('is_active', $link->is_active))>
                <label for="is_active" class="label-syndicate">Toggle</label>
            </div>
            <div class="filter-toggle-text">Active</div>
        </div>
    </div>
</div>
