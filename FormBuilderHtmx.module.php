<?php namespace ProcessWire;

class FormBuilderHtmx extends WireData implements Module
{
    public static function getModuleInfo()
    {
        return array(
          'title'    => 'HTMX in FormBuilder',
          'version'  => 1,
          'summary'  => 'Enable HTMX in FormBuilder',
          "icon"     => "smile-o",
          "requires" => "FormBuilder>=0.4.5",
          "autoload" => true
        );
    }
    public function ready()
    {


        $this->addHook('/form-builder-htmx/{form}/', function ($e) {
            $form = $e->arguments('form');
            return wire('forms')->render($form);
        });

        $this->addHookBefore("FormBuilderProcessor::render", function($event){
            if($event->object->fbForm->htmx) {
                $form_name = $event->object->formName;
                $this->addHookBefore("InputfieldForm::render", function ($event) use($form_name) {
                    $form = $event->object;
                    $form->attr('hx-post',  "/form-builder-htmx/{$form_name}/");
                    $form->attr('hx-target', "closest .FormBuilder-{$form_name}");
                    $form->attr('hx-select', ".FormBuilder-{$form_name}");
                    $form->attr('hx-trigger', 'submit');
                });
            }
        });


        $this->forms->addHookAfter('ProcessFormBuilder::executeSaveFormSettings', function ($e) {
            $form = $e->arguments(0);
            $editForm = $e->arguments(1);

            $htmx = $editForm->getChildByName('htmx');
            $htmx = $htmx->attr('value');
            $form->htmx = $htmx;

            $e->arguments(0, $form);
        });

        $this->forms->addHookAfter('ProcessFormBuilder::buildEditFormSettings', function ($e) {
            $fbForm = $e->arguments(0);
            $form = $e->return;

            $htmx_enable = new InputfieldCheckbox();
            $htmx_enable->label = "Enable HMTX?";
            $htmx_enable->description = "Enables HTMX when submitting.";
            $htmx_enable->name = "htmx";
            $htmx_enable->attr('checked', $fbForm->htmx ? true : false );
            $form->insertBefore($htmx_enable, $form->get('form_delete'));

            $e->return = $form;
        });
    }
}
