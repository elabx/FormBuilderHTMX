<?php namespace ProcessWire;

class FormBuilderHtmxCSRF extends WireData implements Module
{
    public static function getModuleInfo()
    {
        return array(
          'title'    => 'FormBuilder generate CSRF',
          'version'  => 1,
          'summary'  => 'Enable HTMX in FormBuilder',
          "icon"     => "smile-o",
          "requires" => "FormBuilder>=0.4.5",
          "autoload" => true
        );
    }
    public function ready()
    {

        $this->addHook('/form-builder-htmx-token/{name}', function ($e) {
            // TODO SECURITY
            // Check referrer
            // https://github.com/Neophen/statamic-dynamic-token/blob/master/DynamicToken/DynamicTokenController.php#L20
            $name = $e->arguments('name');
            return $e->session->CSRF->renderInput();
        });
        $this->addHookBefore("FormBuilderProcessor::processInput", function($e){
            $this->addHookBefore("InputfieldForm::processInput", function($e){
                $form = $e->object;
                $form->protectCSRF = true;
                $e->removeHook(null);
            });
        });
        $this->addHookBefore("FormBuilderProcessor::render", function($event){
            if($event->object->fbForm->htmx) {
                $fbForm = $event->object->fbForm;
                $form_name = $event->object->formName;

                $this->addHookBefore("InputfieldForm::render", function ($event) use($fbForm, $form_name) {
                    $form = $event->object;
                     if($fbForm->skipSessionKey){
                        $field = new InputfieldHidden();
                        $field->attr('hx-get', "/form-builder-htmx-token/{$form_name}");
                        $field->attr('hx-trigger', 'revealed');
                        $field->attr('hx-target', "this");
                        $field->attr('hx-select', 'input');
                        $field->attr('hx-swap', 'outerHTML');
                        $form->add($field);
                    }
                    $event->removeHook(null);
                });
            }
        });
    }
}
