# FormBuilderHTMX
Enable HTMX on FormBuilder

1. Make sure you have HTMX loaded in ther markup where you will render the form.
2. Enable HTMX on the form configuration in the Settings tab.

Render form as:

```
echo $forms->render('form_name');
```

Form should now render with the HTMX attributes required to be submitted through AJAX.

If FormBuilderHtmxCSRF is installed, a dynamic CSRF will be requested on the HTMX revelead event on a hidden field of the form. Useful for submitting forms cached through ProCache.
