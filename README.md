# FormBuilderHtmx (WIP)
# Please do not use this in production. 

Enable HTMX on FormBuilder

1. Make sure you have HTMX loaded in ther markup where you will render the form.
2. Enable HTMX on the form configuration in the Settings tab.

Render form as:

```
echo $forms->render('form_name');
```

Form should now render with the HTMX attributes required to be submitted through AJAX.

## FormBuilderHtmxCSRF

If FormBuilderHtmxCSRF is installed, a dynamic CSRF will be requested on the HTMX revelead event on a hidden field of the form. Useful for submitting forms cached through ProCache.

Need to disable the form's CSRF configuration as this module will setup it's own CSRF check.

## Caveats

This module will break Inputfields that requires javascript such as Date with jQuery datepicker as they won't be able to be initialized on the HTMX markup append after POST.
