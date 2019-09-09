
<script type="riot/tag" src="@base('formvalidation:assets/components/formvalidation-fieldsmanager.tag')"></script>

<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/forms')">@lang('Forms')</a></li>
        <li class="uk-active"><span>@lang('Form')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

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

                    <div class="uk-margin">
                        <field-boolean bind="form.experimental_settings" label="@lang('Display experimental settings')"></field-boolean>
                    </div>

                    @trigger('forms.settings.aside')

                </div>
            </div>

            <div class="uk-width-medium-3-4">

                <ul class="uk-tab uk-margin-large-bottom">
                    <li class="{ tab=='fields' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="fields">{ App.i18n.get('Fields') }</a></li>
                    <li class="{ tab=='attributes' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="attributes">{ App.i18n.get('Attributes') }</a></li>
                    <li class="{ tab=='validate' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="validate">{ App.i18n.get('Validate') }</a></li>
                    <li class="{ tab=='responses' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="responses">{ App.i18n.get('Responses') }</a></li>
                    <li class="{ tab=='mailer' && 'uk-active'}" if="{ form.experimental_settings }"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="mailer">{ App.i18n.get('Experimental Mailer Settings') }</a></li>
                </ul>
                
                <div class="uk-form-row" show="{tab=='fields'}">

                    <formvalidation-fieldsmanager bind="form.fields" listoption="true" templates="{ templates }"></cp-fieldsmanager>

                </div>
                
                <div class="uk-form-row" show="{tab=='validate'}">

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

                        <div class="uk-width-medium-1-2" data-idx="{idx}" each="{ field,idx in form.fields }">

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

                <div class="uk-form-row" show="{tab=='attributes'}">

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

                <div class="uk-form-row" show="{tab=='responses'}">

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

                </div>

                <div class="uk-form-row" show="{tab=='mailer'}">

                    @if(isset($app['config']['mailer']))

                        @hasaccess?('cockpit', 'sysinfo')
                            <pre>{{ print_r($app['config']['mailer'], true) }}</pre>
                        @else
                            <p>@lang('Global mailer settings are set').</p>
                        @endif

                    @else
                        <p>@lang('Global Mailer settings are not defined'). @lang('Add them to') <code>config/config.yaml</code>.
                        @hasaccess?('cockpit', 'sysinfo')
                        <a class="uk-button uk-button-small uk-margin-small-left" href="@route('/settings/edit')">@lang('System Settings')</a>
                        @endif
                        </p>
                        <pre><code>mailer:
    from      : noreply@example.com
    from_name : John Doe
    transport : smtp
    host      : smtphost.example.com
    user      : johndoe
    password  : xxpasswordxx
    port      : 587
    auth      : true
    encryption: starttls</code></pre>
                    @endif

                    @hasaccess?('forms', 'manage')
                    <div class="uk-panel uk-panel-box uk-panel-card uk-margin">

                        <label>@lang('Custom mailer settings') (@lang('experimental')) <span class="uk-text-muted">JSON</span> <i class="uk-icon-warning" title="@lang('The mailer settings are stored in the form definitions. Be aware of this fact, if others could access this information.')" data-uk-tooltip></i></label>

                        <field-object bind="form.mailer"></field-object>

                    </div>
                    @endif

                </div>

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

        this.mixin(RiotBindMixin);

        this.form      = {{ json_encode($form) }};
        this.templates = {{ json_encode($templates) }};

        // link collection item, e. g. privacy notice
        this.collections = {{ json_encode(cockpit('collections')->getCollectionsInGroup()) }};
        this.collection  = '';

        this.on('mount', function(){

            this.trigger('update');

            // bind global command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {

                e.preventDefault();
                $this.submit();
                return false;
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

            if(e) e.preventDefault();

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

        this.tab = 'fields';

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

        fromTemplate(template) {

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
                    $this.update();
                }

            });

        }

    </script>

</div>
