# zoo-cart-server-side-plugin
Joomla Zoo cart server side plugin

## Send form
```javascript
(function ($) {
    'use strict';
    var id = 1;
    $('#sm-order-send-form,.sm-feedback-form').on('submit', function () {
        var $form = $(this),
            uniq = 'iframe_unique_' + id,
            data = $form.serialize(),
            iframe = document.createElement('iframe');
   
        iframe.setAttribute('name', uniq);
        iframe.style.display = 'none';

        iframe.onload = function () {
            if (iframe.contentWindow.document.body.innerText) {
                try {                    
                    var resp = JSON.parse(iframe.contentWindow.document.body.innerText);
                    if (resp.data[0]) {
                        if (resp.data[0].error) {
                            return alert(resp.data[0].error);
                        }
                        if (resp.data[0].message) {
                            alert(resp.data[0].message);
                            if (resp.data[0].noreload === undefined) {                                
                                window.location.reload();
                            }
                        }
                    }
                } catch (e) {
                    alert('An error occured when submitting the form');
                }
            }
        };
        $form.attr('target', uniq);
        document.body.appendChild(iframe);
        id += 1;
    });
} (jQuery));
```
for example send order with all items in cart
```php
<form id="sm-order-send-form" action="<?=juri::root()?>index.php?option=com_ajax&plugin=sendorder&group=zoo&format=json" method="post"  enctype="multipart/form-data">
     <input type="text" name="fio"/>
     <input type="mail" name="mail"/>
     <input type="tel" name="phone"/>
     <input type="file" multyple name="attach[]">
     <textarea require name="message"></textarea>
     <input type="submit" value="send">
<!-- input -->
</form>
```
in result to admin will send mail with info about order. Template for letter [here](https://github.com/xdan/zoo-cart-server-side-plugin/blob/master/order.php)
or feedback
```php
<form id="sm-order-send-form" action="<?=juri::root()?>index.php?option=com_ajax&plugin=feedback&group=zoo&format=json" method="post"  enctype="multipart/form-data">
     <input type="text" name="fio"/>
     <input type="mail" name="mail"/>
     <input type="tel" name="phone"/>
     <input type="file" multyple name="attach[]">
     <textarea require name="message"></textarea>
     <input type="submit" value="send">
<!-- input -->
</form>
```
in result to admin will send mail with info about message. Template for letter [here](https://github.com/xdan/zoo-cart-server-side-plugin/blob/master/feedback.php)
## Add item in cart
```
index.php?option=com_ajax&plugin=zoocart&group=zoo&format=json&id=111
```
Response
```json
{"success":true,"message":null,"messages":null,"data":[{"error":false,"summ":700,"count":7}]}
```
## Del item by cart
```
index.php?option=com_ajax&plugin=delcart&group=zoo&format=json&id=111
```
Response
```json
{"success":true,"message":null,"messages":null,"data":[{"error":false,"summ":700,"count":7}]}
```
## Update item count in cart
```
index.php?option=com_ajax&plugin=setcount&group=zoo&format=json&id=111
```
Response
```json
{"success":true,"message":null,"messages":null,"data":[[[{"item_id":"6","count":8,"price":"100"}],{"error":false,"summ":800,"count":8}]]}
```
## Clear cart
```
index.php?option=com_ajax&plugin=clear&group=zoo&format=json
```

For all command will calc price. For this must be created position `prices` and layout `prices`
```
<positions layout="prices">
    <position name="prices">Цены</position>
</positions>
```
And in this position must be [price elements](https://github.com/xdan/zoo-custom-type-input-element) with filled Ext field. 
Example
```
element 1 
Price 500 Ext 1
element 2 
Price 400 Ext 10
```
After send 5 count of items in cart. Price will calc by first element. But then element s will >= 10 then price calc from element 2
