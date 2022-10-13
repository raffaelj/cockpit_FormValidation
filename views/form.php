<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/forms')">@lang('Forms')</a></li>
        <li class="uk-active"><span>@lang('Form')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

<style>
[data-tab] .uk-grid > [data-idx]:focus-within > .uk-panel-card {
    box-shadow: 0 0px 10px 0 rgba(0,0,0,0.5);
}
</style>

    <form class="uk-form" onsubmit="{ submit }">

        <div class="uk-grid">

            <div class="uk-width-medium-1-4">
                <div class="uk-panel uk-panel-box uk-panel-card">

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Name')</label>
                        <input aria-label="@lang('Name')" class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="form.name" pattern="[a-zA-Z0-9_]+" required>
                        <p class="uk-text-small uk-text-muted" if="{!form._id}">
                            @lang('Only alpha nummeric value is allowed')
                        </p>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Label')</label>
                        <input aria-label="@lang('Label')" class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.label">
                    </div>

                    <div class="uk-margin">
                       <label class="uk-text-small">@lang('Icon')</label>
                       <div data-uk-dropdown="pos:'right-center', mode:'click'">
                           <a><img class="uk-display-block uk-margin uk-container-center" riot-src="{ form.icon ? '@url('assets:app/media/icons/')'+form.icon : '@url('forms:icon.svg')'}" alt="icon" width="100"></a>
                           <div class="uk-dropdown uk-dropdown-scrollable uk-dropdown-width-2">
                                <div class="uk-grid uk-grid-gutter">
                                    <div>
                                        <a class="uk-dropdown-close" onclick="{ selectIcon }" icon=""><img src="@url('forms:icon.svg')" width="30" icon=""></a>
                                    </div>
                                    @foreach($app->helper("fs")->ls('*.svg', 'assets:app/media/icons') as $icon)
                                    <div>
                                        <a class="uk-dropdown-close" onclick="{ selectIcon }" icon="{{ $icon->getFilename() }}"><img src="@url($icon->getRealPath())" width="30" icon="{{ $icon->getFilename() }}"></a>
                                    </div>
                                    @endforeach
                                </div>
                           </div>
                       </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Color')</label>
                        <div class="uk-margin-small-top">
                            <field-colortag bind="form.color" title="@lang('Color')" size="20px"></field-colortag>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Description')</label>
                        <textarea aria-label="@lang('Description')" class="uk-width-1-1 uk-form-large" name="description" bind="form.description" rows="5"></textarea>
                    </div>

                    <div class="uk-margin">
                        <label aria-label="@lang('Email')" class="uk-text-small">@lang('Email')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_forward">

                        <div class="uk-alert">
                            @lang('Leave the email field empty if you don`t want to recieve any form data via email.')
                        </div>
                    </div>

                    <div class="uk-margin">
                        <field-boolean bind="form.save_entry" label="@lang('Save form data')"></field-boolean>
                    </div>

                    <div class="uk-margin">
                        <field-boolean bind="form.validate_and_touch_data" label="@lang('Validate and touch data')"></field-boolean>
                        <i class="uk-icon uk-icon-info" title="@lang('Before performing any checks, the submitted data will be cleaned by the validator (1.: trim, 2.: strip_tags, 3.: htmlspecialchars)')" data-uk-tooltip></i>
                    </div>

                    @trigger('forms.settings.aside')

                </div>
            </div>

            <div class="uk-width-medium-3-4">

                <ul class="uk-tab uk-margin-large-bottom">
                    <li class="{ tab=='layout' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="layout">{ App.i18n.get('Layout') }</a></li>
                    <li class="{ tab=='validate' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="validate">{ App.i18n.get('Validations') }</a></li>
                    <li class="{ tab=='attributes' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="attributes">{ App.i18n.get('HTML Attributes') }</a></li>
                    <li class="{ tab=='responses' && 'uk-active'}"><a class="" onclick="{ toggleTab }" data-tab="responses">{ App.i18n.get('Responses') }</a></li>
                    <li><a class="" onclick="{showEntryObject}">@lang('Json')</a></li>
                </ul>

                <div class="uk-form-row" show="{tab=='layout'}" data-tab="layout">

                    <div ref="fieldscontainer" class="uk-sortable uk-grid uk-grid-small uk-grid-gutter uk-form">

                        <div class="uk-width-{field.width}" data-idx="{ idx }" each="{ field,idx in form.fields }">

                            <div class="uk-panel uk-panel-box uk-panel-card">

                                <div class="uk-grid uk-grid-small">

                                    <div class="uk-flex-item-1 uk-flex">
                                        <img class="uk-margin-small-right" riot-src="{ fieldIcons[field.type] }" alt="" width="20" height="20" if="{ fieldIcons[field.type] }">
                                        <input class="uk-flex-item-1 uk-form-small uk-form-blank" type="text" bind="form.fields[{idx}].name" placeholder="name" pattern="[a-zA-Z0-9_]+" required>
                                    </div>

                                    <div class="uk-flex-item-2 uk-flex" if="{ field.options.link }">
                                        <span class="uk-icon-link uk-margin-small-top" title="{ App.i18n.get('has linked item') }" data-uk-tooltip></span>
                                    </div>

                                    <div class="uk-width-1-4">
                                        <div class="uk-form-select" data-uk-form-select>
                                            <div class="uk-form-icon">
                                                <i class="uk-icon-arrows-h"></i>
                                                <input class="uk-width-1-1 uk-form-small uk-form-blank" value="{ field.width }">
                                            </div>
                                            <select bind="form.fields[{idx}].width">
                                                <option value="1-1">1-1</option>
                                                <option value="1-2">1-2</option>
                                                <option value="1-3">1-3</option>
                                                <option value="2-3">2-3</option>
                                                <option value="1-4">1-4</option>
                                                <option value="3-4">3-4</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="uk-text-right">

                                        <ul class="uk-subnav">

                                            <li>
                                                <a class="uk-text-{ field.lst ? 'success':'muted'}" onclick="{ togglelist }" title="{ App.i18n.get('Show field on list view') }">
                                                    <i class="uk-icon-{ field.lst ? 'eye':'eye-slash'}"></i>
                                                </a>
                                            </li>

                                            <li>
                                                <a onclick="{ fieldSettings }"><i class="uk-icon-cog uk-text-primary"></i></a>
                                            </li>

                                            <li>
                                                <a class="uk-text-danger" onclick="{ removefield }">
                                                    <i class="uk-icon-trash"></i>
                                                </a>
                                            </li>

                                        </ul>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="uk-form-row" if="{tab=='validate'}" data-tab="validate">

                    <div class="uk-grid uk-grid-small uk-panel uk-margin">

                        <div class="">
                            <field-boolean bind="form.validate" label="@lang('Validate form data')"></field-boolean>
                        </div>

                        <div class="">
                            <field-boolean bind="form.allow_extra_fields" label="@lang('Allow extra fields')"></field-boolean>
                            <i class="uk-icon uk-icon-warning" title="@lang('If enabled, all posted data with unknown field names will come through.')" data-uk-tooltip></i>
                        </div>

                    </div>

                    <div class="uk-margin uk-grid uk-grid-small uk-grid-gutter">

                        <div class="uk-width-large-1-2" data-idx="{idx}" each="{ field,idx in form.fields }">

                            <div class="uk-panel uk-panel-box uk-panel-card">

                                <div class="uk-grid uk-grid-small">

                                    <div class="uk-flex-item-1 uk-flex">
                                        <p class="uk-text-bold">{ field.name }</p>
                                    </div>

                                    <div class="uk-flex-item-2 uk-flex">
                                        <field-boolean bind="form.fields[{idx}].required" label="@lang('Required')"></field-boolean>
                                    </div>

                                    <div class="uk-flex-item-2 uk-flex">
                                        <field-boolean bind="form.fields[{idx}].validate" label="@lang('Validate')"></field-boolean>
                                    </div>

                                    <div class="uk-margin uk-width-1-1" if="{form.fields[idx].validate}">
                                        <field-object bind="form.fields[{idx}].options.validate" label="@lang('Options')" height="210px"></field-object>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="uk-form-row" if="{tab=='attributes'}" data-tab="attributes">

                    <div class="uk-margin-bottom">
                        <label>@lang('Define some attributes for your frontend.')</label>
                    </div>

                    <div class="uk-margin uk-grid uk-grid-small uk-grid-gutter">

                        <div class="uk-width-medium-1-2" data-idx="{idx}" each="{ field,idx in form.fields }">

                            <div class="uk-panel uk-panel-box uk-panel-card">

                                <label class="uk-text-bold">{ field.name }</label>

                                <div class="uk-margin uk-width-1-1">

                                    <field-object bind="form.fields[{idx}].options.attr" label="@lang('Options')" height="210px"></field-object>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="uk-form-row" show="{tab=='responses'}" data-tab="responses">

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                        <label class="uk-text-small">@lang('Email subject')</label>

                        <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_subject">

                        <div class="uk-alert">
                            @lang('Use double brackets to use app.name or form field contents as template.') @lang('Example'): <code>[\{\{app.name\}\}] \{\{subject\}\}"</code>
                        </div>

                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                        <label class="uk-text-small">@lang('Reply To')</label>

                        <select bind="form.reply_to">
                            <option value=""></option>
                            <option value="{ field.name }" each="{ field, idx in form.fields }">{ field.name }</option>
                        </select>

                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                        <p>
                            @lang('Create a custom mail template, to use the settings below.') @lang('Save it as') <code>config/forms/emails/formname.php</code>
                            (<a href="https://github.com/raffaelj/cockpit_FormValidation/blob/master/templates/emails/contactform.php" target="bubble" title="@lang('external link')" data-uk-tooltip>@lang('Example')</a>)

                            <a class="uk-button" href="#" onclick="{copyMailTemplate}"><i class="uk-icon-copy uk-margin-small-right"></i>@lang('Copy default mail template to config directory')</a>

                        </p>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Text before mail')</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_text_before">

                            <div class="uk-alert">
                                @lang('Use double brackets to use app.name, site_url or form field contents as template.') @lang('Example'): <code>New message on \{\{site_url\}\} from \{\{name\}\}</code>
                            </div>

                        </div>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Text after mail')</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_text_after">

                            <div class="uk-alert">
                                @lang('Use double brackets to use app.name, site_url or form field contents as template.') @lang('Example'): <code>Have a nice day and don't forget to send \{\{item\}\} to \{\{name\}\}.</code>
                            </div>

                        </div>
                    </div>

                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                        <label class="uk-text-small">@lang('Custom form massages')</label>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Success massage')</label>

                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.formMessages.success">

                        </div>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Notice massage')</label>

                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.formMessages.notice">

                        </div>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Mailer error massage')</label>

                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.formMessages.mailer">

                        </div>

                        <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                            <label class="uk-text-small">@lang('Generic error massage')</label>

                            <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.formMessages.error_generic">

                        </div>
                    </div>

                </div>

                <div class="uk-margin-top"">
                    <a class="uk-button uk-button-outline uk-text-primary" onclick="{ addfield }" if="{ form.fields.length }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add field') }</a>

                    <span data-uk-dropdown="pos:'bottom-center'" if="{ !form.fields.length && !reorder }">
                        <a class="uk-button uk-button-outline uk-text-primary" onclick="{ addfield }">{ App.i18n.get('Add field') }.</a>
                        <div class="uk-dropdown uk-dropdown-scrollable uk-text-left" if="{templates && templates.length}">
                            <ul class="uk-nav uk-nav-dropdown">
                                <li class="uk-nav-header">{ App.i18n.get('Choose from template') }</li>
                                <li each="{template in templates}">
                                    <a onclick="{ fromTemplate }"><i class="uk-icon-sliders uk-margin-small-right"></i> { template.label || template.name }</a>
                                </li>
                            </ul>
                        </div>
                    <span>

                </div>

                <div class="uk-modal uk-sortable-nodrag" ref="modalField">
                    <div class="uk-modal-dialog uk-modal-dialog-large" if="{field}">

                        <div class="uk-form-row uk-text-large uk-text-bold">
                            <img class="uk-margin-small-right" riot-src="{ fieldIcons[field.type] }" alt="" width="20" height="20" if="{ fieldIcons[field.type] }">
                            { field.name || 'Field' }
                        </div>

                        <div class="uk-tab uk-flex uk-flex-center uk-margin" data-uk-tab>
                            <li class="uk-active"><a>{ App.i18n.get('General') }</a></li>
                            <li><a>{ App.i18n.get('Advanced') }</a></li>
                        </div>

                        <div class="uk-margin-top ref-tab">

                            <div>
                                <div class="uk-grid uk-grid-gutter">

                                    <div class="uk-width-medium-1-2">

                                        <div class="uk-form-row">
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Name') }:</label>
                                            <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.name" placeholder="name" pattern="[a-zA-Z0-9_]+" required>
                                        </div>

                                        <div class="uk-form-row">
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Label') }:</label>
                                            <input class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.label" placeholder="{ App.i18n.get('Label') }">
                                        </div>

                                        <div class="uk-form-row">
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Info') }:</label>
                                            <textarea class="uk-width-1-1 uk-margin-small-top" bind="field.info" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <div class="uk-width-medium-1-2">

                                        <div class="uk-grid">

                                            <div class="uk-width-small-1-2">
                                                <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Type') }:</label>
                                                <div class="uk-form-select uk-width-1-1 uk-margin-small-top">
                                                    <a class="uk-text-capitalize">{ field.type }</a>
                                                    <select class="uk-width-1-1 uk-text-capitalize" bind="field.type">
                                                        <option each="{type,typeidx in fieldtypes}" value="{type.value}">{type.name}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="uk-width-small-1-2">

                                                <div class="uk-form-row">
                                                    <field-boolean bind="field.required" label="{ App.i18n.get('Required') }"></field-boolean>
                                                </div>

                                                <div class="uk-form-row">

                                                    <field-boolean bind="field.validate" label="{ App.i18n.get('Validate') }" class=""></field-boolean>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="uk-form-row" if="{ ['select','multipleselect'].includes(field.type) && riot.tags['field-key-value-pair'] }">
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Options') }:</label>
                                            <field-key-value-pair class="uk-width-1-1 uk-margin-small-top" type="text" bind="field.options.options"></field-key-value-pair>
                                        </div>

                                        <div class="uk-form-row" if="{ field.type == 'honeypot' }">
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Field Options') }:</label>
                                            <a class="uk-button" onclick="{ setHoneyPotOptions }">{App.i18n.get('Set default Honey pot options')}</a>
                                        </div>

                                        <div class="uk-form-row" if="{ field.type == 'contentblock' }">
<!--                                            <raw content="{ field.content }"></raw>-->
                                            <label class="uk-text-muted uk-text-small">{ App.i18n.get('Add text blocks between form elements') }:</label>
                                            <cp-fieldcontainer>
                                            <field-wysiwyg bind="field.content"></field-wysiwyg>
                                            </cp-fieldcontainer>
                                        </div>

                                    </div>
                                </div>

                            </div>
                            <div class="uk-hidden">

                                <div class="uk-form-row">
                                    <label class="uk-text-small uk-text-bold uk-margin-small-bottom">{ App.i18n.get('Options') } <span class="uk-text-muted">JSON</span></label>
                                    <field-object cls="uk-width-1-1" bind="field.options" rows="6" allowtabs="2"></field-object>
                                </div>

                                <div class="uk-form-row">
                                    <label class="uk-text-small uk-text-bold uk-margin-small-bottom">{ App.i18n.get('Link Collection Item') }</label>

                                    <select bind="collection">
                                        <option value=""></option>
                                        <option value="{ col.name }" each="{ col in collections }">{ col.label || col.name }</option>
                                    </select>

                                    <div class="uk-grid uk-grid-small uk-grid-match uk-margin uk-width-1-1">

                                        <div class="uk-width-1-3">
                                            <label>{ App.i18n.get('Text before link') }</label>
                                            <field-textarea rows="2" bind="field.options.link.text_before" if="{field.options.link}"></field-textarea>
                                        </div>

                                        <div class="uk-width-1-3">
                                            <label>{ App.i18n.get('Collectionlink') }</label>
                                            <field-collectionlink link="{ collection }" class="" bind="field.options.link" if="{collection}"></field-collectionlink>
                                        </div>

                                        <div class="uk-width-1-3">
                                            <label>{ App.i18n.get('Text after link') }</label>
                                            <field-textarea rows="2" bind="field.options.link.text_after" if="{field.options.link}"></field-textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-large uk-button-link uk-modal-close">{ App.i18n.get('Close') }</button></div>

                    </div>
                </div>

                <cp-inspectobject ref="inspect"></cp-inspectobject>

                <cp-actionbar>
                    <div class="uk-container uk-container-center">

                        <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>
                        <a class="uk-button uk-button-large" href="@route('/forms/entries')/{ form.name }" if="{ form._id }">@lang('Show entries')</a>

                        <a class="uk-button uk-button-large uk-button-link" href="@route('/forms')">
                            <span show="{ !form._id }">@lang('Cancel')</span>
                            <span show="{ form._id }">@lang('Close')</span>
                        </a>
                    </div>
                </cp-actionbar>
            </div>
        </div>
    </form>

    <script type="view/script">

        var $this = this;
        var modal;

        this.mixin(RiotBindMixin);

        this.form      = {{ json_encode($form) }};
        this.templates = {{ json_encode($templates) }};
        this.reorder = false;
        this.tab = 'layout';
        this.field = null;

        // link collection item, e. g. privacy notice
        this.collections = {{ json_encode(cockpit('collections')->getCollectionsInGroup()) }};
        this.collection  = '';

        if (!this.form.fields || !Array.isArray(this.form.fields)) {
            this.form.fields = [];
        }

        this.fieldtypes = [
            {
                name: 'Text',
                value:'text',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/text.svg") }}'
            },
            {
                name: 'Textarea',
                value:'textarea',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/text.svg") }}'
            },
            {
                name: 'Date',
                value:'date',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/date.svg") }}'
            },
            {
                name: 'Boolean',
                value:'boolean',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/boolean.svg") }}'
            },
            {
                name: 'Select',
                value:'select',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/select.svg") }}'
            },
            {
                name: 'Honeypot',
                value:'honeypot',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/settings.svg") }}'
            },
            {
                name: 'Multipleselect',
                value:'multipleselect',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/select.svg") }}'
            },
            {
                name: 'Content Block',
                value:'contentblock',
                icon: '{{ $app->baseUrl("formvalidation:assets/icons/wysiwyg.svg") }}'
            },
        ];
        // sort by field name
        this.fieldtypes = this.fieldtypes.sort(function(fieldA, fieldB) {
            return fieldA.name.localeCompare(fieldB.name);
        });

        this.fieldIcons = {};
        this.fieldtypes.forEach(function(e) {
            if (e.icon) $this.fieldIcons[e.value] = e.icon;
        });

        this.on('mount', function(){

            this.trigger('update');

            // bind global command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
            });

            UIkit.sortable(this.refs.fieldscontainer, {

                dragCustomClass:'uk-form'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                if (App.$(e.target).is(':input')) {
                    return;
                }

                ele = App.$(ele);

                var fields = $this.form.fields,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                fields.splice(cidx, 0, fields.splice(oidx, 1)[0]);

                // hack to force complete fields rebuild
                App.$($this.refs.fieldscontainer).css('height', App.$($this.refs.fieldscontainer).height());

                $this.fields = [];
                $this.reorder = true;
                $this.update();

                setTimeout(function() {
                    $this.reorder = false;
                    $this.form.fields = fields;
                    $this.update();

                    setTimeout(function(){
                        $this.refs.fieldscontainer.style.height = '';
                    }, 30)
                }, 0);

            });

            modal = UIkit.modal(this.refs.modalField);
            modal.on({
                'hide.uk.modal': function(){
                    $this.form.fields.forEach(function(field) {
                        if (Array.isArray(field.options)) {
                            field.options = {};
                        }
                    });
                    $this.field = null;
                    $this.update();
                }
            });

            App.$(this.root).on('click', '.uk-modal [data-uk-tab] li', function(e) {
                var item = App.$(this),
                    idx = item.index();

                item.closest('.uk-tab')
                    .next('.ref-tab')
                    .children().addClass('uk-hidden').eq(idx).removeClass('uk-hidden')
            });

        });

        this.on('update', function(){
            // lock name if saved
            if (this.form._id) {
                this.refs.name.disabled = true;
            }
        });

        selectIcon(e) {
            this.form.icon = e.target.getAttribute('icon');
        }

        submit(e) {

            if (e) e.preventDefault();

            var form = this.form;

            App.callmodule('forms:saveForm', [this.form.name, form]).then(function(data) {

                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.form = data.result;

                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
        }

        toggleTab(e) {
            this.tab = e.target.getAttribute('data-tab');
        }

        copyMailTemplate() {

            App.request('/formvalidation/copyMailTemplate/' + this.form.name).then(function(data) {

                if (data && !data.error) {
                    App.ui.notify("Copied mail template to config dir", "success");
                } else if (data && data.error) {
                    App.ui.notify(data.error, "danger");
                } else {
                    App.ui.notify("Copying failed.", "danger");
                }

            });

        }

        fromTemplate(e) {

            var template = e.item.template;

            if (template && Array.isArray(template.fields) && template.fields.length) {
                this.form.fields = template.fields;
            }

            var options = [
                'save_entry',
                'in_menu',
                'email_forward',
                'icon',
                'validate',
                'allow_extra_fields',
                'email_subject',
                'reply_to',
                'email_text_before',
                'email_text_after',
            ];

            options.forEach(function(option) {

                if (typeof template[option] !== 'undefined') {
                    $this.form[option] = template[option];
                }

            });

            this.update();

        }

        showEntryObject() {
            $this.refs.inspect.show($this.form);
            $this.update();
        }

        addfield() {

            this.form.fields.push({
                'name'    : '',
                'label'   : '',
                'type'    : 'text',
                'default' : '',
                'info'    : '',
                'options' : {},
                'width'   : '1-1',
                'lst'     : true,
//                 'acl'     : []
            });

            this.update();

            lastFieldIndex = this.form.fields.length - 1;

            var el = document.querySelector('[data-tab="'+this.tab+'"] .uk-grid > [data-idx="'+lastFieldIndex+'"]');

            el.querySelector('input').focus();

        }

        removefield(e) {
            this.form.fields.splice(e.item.idx, 1);
        }

        fieldSettings(e) {

            this.field = e.item.field;

            if (this.field.options && this.field.options.link && this.field.options.link.link) {
                this.collection = this.field.options.link.link;
            }

            modal.show();

        }

        togglelist(e) {
            e.item.field.lst = !e.item.field.lst;
        }

        this.honeypot_options = {
          "attr": {
            "name": "confirm",
            "id": "confirm",
            "value": "1",
            "style": "display:none !important",
            "tabindex": "-1"
          },
          "validate": {
            "honeypot": {
              "fieldname": "confirm",
              "expected_value": "0",
              "response": "Spam bots are not welcome here."
            }
          }
        };

        setHoneyPotOptions(e) {
            if (e) e.preventDefault();
            e.item.field.options = this.honeypot_options;
            e.item.field.validate = true;
            this.update();
        }

    </script>

</div>
