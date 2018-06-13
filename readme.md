# Installation #

### install by composer ###
<pre><code>
composer require virtualorz/contact
</code></pre>

### edit config/app.php ###
<pre><code>
'providers' => [
    ...
    Virtualorz\Contact\ContactServiceProvider::class,
    Virtualorz\Pagination\PaginationServiceProvider::class,
    ...
]

'aliases' => [
    ...
    'Contact' => Virtualorz\Contact\ContactFacade::class,
    'Pagination' => Virtualorz\Pagination\PaginationFacade::class,
    ...
]
</code></pre>

### migration db table ###
<pre><code>
php artisan migrate
</code></pre>

### publish config ###
<pre><code>
php artisan vendor:publish --provider="Virtualorz\Contact\ContactServiceProvider"
</code></pre>

### edit config of admin email ###
<pre><code>
'admin_email' => 'your admin email address here'
</code></pre>


# usage #
#### 1. get cate list data ####
<pre><code>
$dataSet = Contact::list();
</code></pre>
$dataSet : return date

#### 2. add data to cate ####
<pre><code>
Contact::add();
</code></pre>
with request variable name required : contact-name,contact-company,contact-tel,contact-email,contact-message

#### 3. get cate detail ####
<pre><code>
$dataRow = Contact::detail($contact_id,$is_backend);
$is_backend : if backend get detail set 1 to change status to 1
</code></pre>

#### 4. edit data to cate ####
<pre><code>
Contact::reply();
</code></pre>
with request variable name required : contact_reply-content

#### 5. delete cate data ####
<pre><code>
Contact::delete();
</code></pre>
with request variable name required : id as integer or id as array




