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

        $this->addHook('/form-builder-htmx-token/', function ($e) {
            return $e->session->CSRF->renderInput();
        });

        $this->addHookBefore("FormBuilderProcessor::render", function($event){
            if($event->object->fbForm->htmx) {
                $fbForm = $event->object->fbForm;
                $form_name = $event->object->formName;

                $this->addHookBefore("InputfieldForm::render", function ($event) use($fbForm, $form_name) {
                    $form = $event->object;
                    if(!$fbForm->protectCSRF){
                        $field = new InputfieldHidden();
                        $field->attr('hx-get', '/form-builder-htmx-token/');
                        $field->attr('hx-trigger', 'revealed');
                        $field->attr('hx-target', 'this');
                        $form->add($field);
                    }
                });
            }

        });
    }
}
