# Blade Components

Read more about how controllers work by going [here]('https://github.com/soberwp/controller').

All controllers for this project can be found in `/iresq-theme/app/Controllers`

#

### Controller purpose and how to use them

Controllers are used to create function for a specific view. They keep all logic out of the View files.

If your view file is named `page-contact-us.blade.php`, then your controller file must be named `PageContactUs.php` and that files class name must follow the same criteria. Here's what that file might look like:

    <?php

    namespace App\Controllers;

    use Sober\Controller\Controller;

    class PageContactus extends Controller
    {
      ...
    }

#

### Creating methods and variables in a controller

- If you want to create a method that returns the site name to your View you would use a `public` function like this:

        public function siteName()
        {
            return get_bloginfo('name');
        }

  - The function name will be converted to snake case in the View file, so you would be able to use it in your view file like this

          <h1>{{ $site_name }}</h1>

- If you want to create a method that will be called multiple times within a loop, use a `static` function instead.

        public static function postTitle()
        {
          return get_post->post_title;
        }

  - Static functions will not be converted to snake case and you must call the class name before the method. Here's how you would use it in your View file

          @while ( have_posts() ) @php the_post() @endphp
            {{ PageContactus::postTitle() }}
          @endwhile

- If you want to create a method that will only be used internally and you don't want to give your View file access to it, then use a `protected` function.

#

### Passing data using Advanced Custom Fields

- Controller has a built-in module to automate passing on ACF fields. The automated fields will use the variable names from ACF and pass them onto the view. The enable this just write this line in your controller

        protected $acf = true;

  - If you only want to pass on select fields then declare the `$acf` variable like this

            protected $acf = ['field_1', 'field_2'];

  - Then in your View file you would have full access to these fields

            <div>{{ $field_1 }}</div>

* The `$acf` variable will not work within the Wordpress post loop. If you have any ACF fields to pass on to posts then you'll need to write methods to grab those values manually

        public static function getField1() {
          return get_field('field_1');
        }

  - Then in your View file...

            @while ( have_posts() ) @php the_post() @endphp
              <span>{{ PageContactus::getField1() }}</span>
            @endwhile
