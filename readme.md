# WordPress Email
A WordPress library to simplify sending emails.

## What It Does
While WordPress does have the wp_mail() function, there are still a number of
things you have to be aware of when sending emails. For example:

* You have to add a special filter to send an HTML email.
* You have to add a special filter to set the from name and/or email.
* Special characters in the email subject line or from name may not show properly if not properly decoded.
* To add CC or BCC recipients to an email, you have to add special headers.
* Email recipients have to be properly formatted if you want to include a name.
* Adding filters can impact other emails sent via the system.

This library removes these concerns and makes it easy to setup and send emails
without impacting other emails being sent from the system.

## How to Use It

1. Add to your project via [Composer](https://getcomposer.org/):

```bash
$ composer require wpscholar/wp-email
```

2. Make sure you have added the Composer autoloader to your project:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

3. Create a new email:

```php
<?php

use wpscholar\WordPress\Email;

// Create new email instance
$email = new Email();

// Set subject and message
$email->subject = 'Welcome!';
$email->message = '<p>Lorem ipsum dolor sit amet...</p>';

// Customize the from name and email
$email->from('John Doe <john@email.com>');

// Add any recipients
$email->addRecipient('Jane Doe <jane@email.com>');
$email->addCcRecipient('James Doe <james@email.com>');
$email->addBccRecipient('Super Spy <topsecret@email.com>');

// Add any attachments
$email->addAttachment( '/wp-content/uploads/attachment.pdf' );

// Send email
$email->send();
```

OR

```php
<?php

use wpscholar\WordPress\Email;

$email = new Email([
    'subject' => 'Welcome!',
    'message' => '<p>Lorem ipsum dolor sit amet...</p>',
    'from' => 'John Doe <john@email.com>',
    'to' => ['Jane Doe <jane@email.com>'],
    'cc' => ['James Doe <james@email.com>'],
    'bcc' => ['Super Spy <topsecret@email.com>'],
    'attachments' => ['/wp-content/uploads/attachment.pdf'],
]);

$email->send();
```
