# Blade Components

Read more about how components work by going [here]('https://laravel.com/docs/5.8/blade#components-and-slots').

All component templates for this site can be found in `/resources/views/components`.

#

### Rendering components

You can render components by using the laravel component snippet:

    @component('component-name', ['key' => 'value'])
      ...
    @endcomponent

The first argument is the component name and that needs to match the file name. The second argument is an array of additional data to pass to the component. You can pass data in as an array, or you can use the blade `@slot` snippet.

For example, if you have a component file named `text-field.blade.php` and that component takes two variables, **\$id** and **\$text**, the render would look like this:

    @component('text-field', ['id' => 'homepage-text'])
      @slot('text')
        Some sample text
      @endslot
    @endcomponent

This renders the component and gives that component file two variables ($id and $text) to use. The text-field component file may look something like this:

    <span class="text-field" id={{ $id }}>
      {!! $text !!}
    </span>

#

### Aliasing Components

Once you've created a component, you can give it an **alias** in `/iresq-theme/app/setup.php`. This makes files easier to understand when multiple components are used on a single. Continuing with the `text-field.blade.php` example, you need to go into the `setup.php` file and find the `after_setup_theme` action. At the bottom you write:

    sage('blade')->compiler()->component('components.text-field', 'textfield');

The first argument in is the filename for your component, and the second is the alias you wish to give your component. So now your text-field component render would look something like this

    @textfield(['id' => 'homepage-text'])
      @slot('text')
        Some sample text
      @endslot
    @endtextfield

#

### Component data

_In order to make your components clear to another developer, document what variables are needed to render your component in the component file._

Most of the data that you'll be using in this project will come from Advanced Custom Fields (see controller/ACF docs).
