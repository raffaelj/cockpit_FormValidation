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
                        <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="form.name" pattern="[a-zA-Z0-9_]+" required>
                        <p class="uk-text-small uk-text-muted" if="{!form._id}">
                            @lang('Only alpha nummeric value is allowed')
                        </p>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Label')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.label">
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
                        <textarea class="uk-width-1-1 uk-form-large" name="description" bind="form.description" rows="5"></textarea>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-text-small">@lang('Email')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_forward">

                        <div class="uk-alert">
                            @lang('Leave the email field empty if you don`t want to recieve any form data via email.')
                        </div>
                    </div>

                    <div class="uk-margin">
                        <field-boolean bind="form.in_menu" label="@lang('Show in system menu')"></field-boolean>
                    </div>

                    <div class="uk-margin">
                        <field-boolean bind="form.save_entry" label="@lang('Save form data')"></field-boolean>
                    </div>

                </div>
            </div>

            <div class="uk-width-medium-3-4">
               <div class="uk-panel uk-panel-box uk-panel-card">

                <ul class="uk-tab uk-margin-large-bottom">
                    <li class="{ tab=='fields' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="fields">{ App.i18n.get('Fields') }</a></li>
                    <li class="{ tab=='validate' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="validate">{ App.i18n.get('Validate') }</a></li>
                    <li class="{ tab=='responses' && 'uk-active'}"><a class="uk-text-capitalize" onclick="{ toggleTab }" data-tab="responses">{ App.i18n.get('Responses') }</a></li>
                </ul>
                
                <div class="uk-form-row" show="{tab=='fields'}">

                    <cp-fieldsmanager bind="form.fields" listoption="true" templates="{ templates }"></cp-fieldsmanager>

                </div>
                
                <div class="uk-form-row" show="{tab=='validate'}">

                    <div class="uk-grid">
                      <div class="uk-flex-item-2 uk-flex">
                          <field-boolean bind="form.validate" label="@lang('Validate form data')"></field-boolean>
                      </div>

                      <div class="uk-flex-item-2 uk-flex">

                          <field-boolean bind="form.allow_extra_fields" label="@lang('Allow extra fields')"></field-boolean>
                          
                      </div>
                    </div>

                    
                    <div class="uk-margin" if="{form.validate}">
                        <!--<field-object bind="form.validate_options" label="@lang('Options')"></field-object>-->
                        
                        <div class="uk-margin uk-panel uk-panel-box uk-panel-card" data-idx="{idx}" each="{ field,idx in form.fields }">
                            
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
                                    
                                    <field-object bind="form.fields[{idx}].options.validate" label="@lang('Options')" height="200px"></field-object>
                                    
                                </div>
                            
                            </div>

                        </div>
                        
                    </div>

                </div>
                
                <div class="uk-form-row" show="{tab=='responses'}">

                    <div class="uk-panel uk-panel-box uk-panel-card">
                        
                        <label class="uk-text-small">@lang('Email subject')</label>
                        <input class="uk-width-1-1 uk-form-large" type="text" name="label" bind="form.email_subject">
                        
                        <div class="uk-alert">
                            @lang('Use double brackets to use form field contents as template, like: "New message from \{\{name\}\}"')
                        </div>

                    </div>
                    
                </div>

            </div>

        </div>

        <div class="uk-margin-large-top">

            <button class="uk-button uk-button-large uk-button-primary">@lang('Save')</button>

            <a class="uk-button uk-button-large uk-button-link" href="@route('/forms')">
                <span show="{ !form._id }">@lang('Cancel')</span>
                <span show="{ form._id }">@lang('Close')</span>
            </a>
        </div>

    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.form = {{ json_encode($form) }};

        this.on('mount', function(){

            this.trigger('update');

            // bind clobal command + save
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
        
        // this.tab = 'fields';
        this.tab = 'validate';

        toggleTab(e) {
            this.tab = e.target.getAttribute('data-tab');
        }

    </script>

</div>
