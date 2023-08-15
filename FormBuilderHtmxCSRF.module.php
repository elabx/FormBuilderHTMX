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
            $name = $e->arguments('name');
            $referer = $_SERVER['HTTP_REFERER'];
            $config = wire('config');
            if(!$referer) throw new Wire404Exception();
            $is_referer = false;
            foreach($config->httpHosts as $host){
                $httpUrl = "http://{$host}";
                $httpsUrl = "https://{$host}";
                $startWithAppUrl = str_starts_with($referer, $httpUrl) || str_starts_with($referer, $httpsUrl);
                if($startWithAppUrl) {
                    $is_referer = true;
                }
            }
            if(!$is_referer) throw new WireException();
            return $e->session->CSRF->renderInput($name);
        });
        $this->addHookBefore("FormBuilderProcessor::processInput", function($e){
            $name = $e->object->formName;
            $processor = $e->object;
            $this->addHookAfter("InputfieldForm::processInput", function($e) use ($name, &$processor){
                /** @var Session $session */
                $session = $e->session;
                try {
                    $session->CSRF->validate($name);
                    $session->CSRF->resetToken($name);
                }catch(\Exception $err){
                    $e->object->error("Invalid submission");
                }
                $e->removeHook(null);
            });
        });
        $this->addHookBefore("FormBuilderProcessor::render", function($event){
            if($event->object->fbForm->htmx) {
                $fbForm = $event->object->fbForm;
                $form_name = $event->object->formName;
                
                $this->addHookBefore("InputfieldForm::render", function ($event) use($fbForm, $form_name) {
                    $form = $event->object;
                    if(!count($form->getErrors())) {
                        if ($fbForm->skipSessionKey) {
                            $field = new InputfieldHidden();
                            $field->attr('hx-get', "/form-builder-htmx-token/{$form_name}");
                            $field->attr('hx-trigger', 'revealed');
                            $field->attr('hx-target', "this");
                            $field->attr('hx-select', 'input');
                            $field->attr('hx-swap', 'outerHTML');
                            $form->add($field);
                        }
                    }
                    $event->removeHook(null);
                });
            }
        });
    }
}
