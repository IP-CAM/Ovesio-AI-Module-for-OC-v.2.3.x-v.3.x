<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
        <input type="hidden" name="hash" value="<?php echo $hash; ?>"/>
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-language-association" data-toggle="tab"><?php echo $tab_language_association; ?></a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="status" id="input-status" class="form-control">
                    <?php if ($status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-api"><?php echo $entry_api; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="api" value="<?php echo $api; ?>" placeholder="<?php echo $entry_api; ?>" id="input-api" class="form-control" />
                  <?php if (!empty($error['api'])) { ?>
                  <span class="text-danger"><?php echo $error['api']; ?></span>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-token"><span data-toggle="tooltip" title="<?php echo $entry_token_helper; ?>"><?php echo $entry_token; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="token" value="<?php echo $token; ?>" placeholder="<?php echo $entry_token; ?>" id="input-token" class="form-control" />
                  <?php if (!empty($error['token'])) { ?>
                  <span class="text-danger"><?php echo $error['token']; ?></span>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-catalog-language"><?php echo $entry_catalog_language; ?></label>
                <div class="col-sm-10">
                  <select name="catalog_language_id" id="input-catalog-language" class="form-control">
                    <?php foreach ($languages as $language) { ?>
                    <option value="<?php echo $language['language_id']; ?>" <?php echo $language['language_id'] == $catalog_language_id ? 'SELECTED' : ''; ?>><?php echo $language['name']; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-language-association">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-translation-status"><?php echo $entry_status; ?></label>
                    <div class="col-sm-10">
                      <select name="translation_status" id="translation-status" class="form-control">
                        <?php if ($translation_status) { ?>
                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                        <option value="0"><?php echo $text_disabled; ?></option>
                        <?php } else { ?>
                        <option value="1"><?php echo $text_enabled; ?></option>
                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-live-translate"><span data-toggle="tooltip" title="<?php echo $help_live_translate; ?>"><?php echo $entry_live_translate; ?></span></label>
                    <div class="col-sm-10">
                        <label class="radio-inline">
                        <input type="radio" name="live_translate" value="1" <?php echo $live_translate ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                        </label>
                        <label class="radio-inline">
                        <input type="radio" name="live_translate" value="0" <?php echo !$live_translate ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                        </label>
                    </div>
                    </div>

                    <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-send-stock-0"><?php echo $entry_send_stock_0; ?></label>
                    <div class="col-sm-10">
                        <label class="radio-inline">
                        <input type="radio" name="send_stock_0" value="1" <?php echo $send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                        </label>
                        <label class="radio-inline">
                        <input type="radio" name="send_stock_0" value="0" <?php echo !$send_stock_0 ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                        </label>
                    </div>
                    </div>

                    <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-send-disabled"><?php echo $entry_send_disabled; ?></label>
                    <div class="col-sm-10">
                        <label class="radio-inline">
                        <input type="radio" name="send_disabled" value="1" <?php echo $send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_enabled; ?>
                        </label>
                        <label class="radio-inline">
                        <input type="radio" name="send_disabled" value="0" <?php echo !$send_disabled ? 'CHECKED' : ''; ?> /> <?php echo $text_disabled; ?>
                        </label>
                    </div>
                </div>

              <fieldset class="table-responsive">
                <legend><?php echo $text_language_association; ?></legend>
                <table class="table table-bordered">
                  <thead>
                    <th><?php echo $text_system_language; ?></th>
                    <th><?php echo $text_iso2_language; ?></th>
                    <th><span class="fa fa-question-circle cursor-pointer" data-toggle="tooltip" title="<?php echo $text_translate_status_helper; ?>"></span> <?php echo $text_translate_status; ?></th>
                    <th><?php echo $text_translate_from; ?></th>
                  </thead>
                  <tbody>
                    <?php foreach ($languages as $language) { ?>
                    <tr>
                      <td><?php echo $language['name']; ?></td>
                      <td>
                        <select name="language_match[<?php echo $language['language_id']; ?>][code]" class="form-control">
                          <option value=""></option>
                          <?php foreach ($iso2 as $_iso2) { ?>
                          <option value="<?php echo $_iso2; ?>" <?php echo $language_match[$language['language_id']]['code'] == $_iso2 ? 'SELECTED' : ''; ?>><?php echo $_iso2; ?></option>
                          <?php } ?>
                        </select>
                        <?php if(!empty($error[$language['language_id']]['code'])) { ?>
                        <span class="text-danger"><?php echo $error[$language['language_id']]['code']; ?></span>
                        <?php } ?>
                      </td>
                      <td>
                        <div class="la-<?php echo $language['language_id']; ?>">
                          <label class="radio-inline">
                            <input type="radio" name="language_match[<?php echo $language['language_id']; ?>][status]" value="1" <?php echo isset($language_match[$language['language_id']]['status']) && $language_match[$language['language_id']]['status'] == 1 ? 'CHECKED' : ''; ?>> <?php echo $text_yes; ?>
                          </label>
                          <label class="radio-inline">
                            <input type="radio" name="language_match[<?php echo $language['language_id']; ?>][status]" value="0" <?php echo !isset($language_match[$language['language_id']]['status']) || $language_match[$language['language_id']]['status'] == 0 ? 'CHECKED' : ''; ?>> <?php echo $text_no; ?>
                          </label>
                        </div>
                      </td>
                      <td>
                        <div class="la-<?php echo $language['language_id']; ?>">
                          <select name="language_match[<?php echo $language['language_id']; ?>][from_language_id]" class="form-control">
                            <?php foreach ($languages as $l) { ?>
                            <option value="<?php echo $l['language_id']; ?>" <?php echo isset($language_match[$language['language_id']]['from_language_id']) && $l['language_id'] == $language_match[$language['language_id']]['from_language_id'] ? 'SELECTED' : ''; ?>><?php echo $l['name']; ?></option>
                            <?php } ?>
                          </select>
                          <?php if(!empty($error[$language['language_id']]['from_language_id'])) { ?>
                          <span class="text-danger"><?php echo $error[$language['language_id']]['from_language_id']; ?></span>
                          <?php } ?>
                        </div>
                      </td>
                      <?php } ?>
                  </tbody>
                </table>
              </fieldset>

              <fieldset>
                <legend><?php echo $text_translated_fields; ?></legend>
                <strong><?php echo $text_products; ?></strong>
                <div class="well well-sm" style="height: 180px; overflow: auto;">
                  <?php foreach ($product_translates as $translate) { ?>
                  <div>
                    <input type="hidden" name="translate_fields[product][<?php echo $translate['key']; ?>]" value="0">
                    <input type="checkbox" name="translate_fields[product][<?php echo $translate['key']; ?>]" value="1" id="product-translate-<?php echo $translate['key']; ?>" <?php echo $translate_fields['product'][$translate['key']] ? 'CHECKED' : ''; ?>> <label for="product-translate-<?php echo $translate['key']; ?>"><?php echo $translate['label']; ?></label>
                  </div>
                  <?php } ?>
                </div>
                <br>
                <strong><?php echo $text_categories; ?></strong>
                <div class="well well-sm" style="height: 180px; overflow: auto;">
                  <?php foreach ($category_translates as $translate) { ?>
                  <div>
                    <input type="hidden" name="translate_fields[category][<?php echo $translate['key']; ?>]" value="0">
                    <input type="checkbox" name="translate_fields[category][<?php echo $translate['key']; ?>]" value="1" id="category-translate-<?php echo $translate['key']; ?>" <?php echo $translate_fields['category'][$translate['key']] ? 'CHECKED' : ''; ?>> <label for="category-translate-<?php echo $translate['key']; ?>"><?php echo $translate['label']; ?></label>
                  </div>
                  <?php } ?>
                </div>
              </fieldset>
              <fieldset>
                <legend><?php echo $text_translate_feeds; ?></legend>
                <div class="well well-sm">
                  <?php foreach ($feeds as $feed) { ?><?php echo $feed; ?><br><?php } ?>
                </div>
              </fieldset>
              <fieldset>
                <legend><?php echo $text_translation_callback; ?></legend>
                <div class="well well-sm">
                  <?php echo $translate_callback; ?>
                </div>
                <?php echo $text_translation_callback_helper; ?>
              </fieldset>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $('#input-catalog-language').on('change', function() {
      $('[class^="la-"]').show();
      var language_id = $(this).val();
      $('[class="la-' + language_id + '"]').hide();
    }).trigger('change');
  });
</script>
<?php echo $footer; ?>