@if (function_exists('rank_math_the_breadcrumbs'))
    <div class="{{ is_single() ? 'tw--mb-16' : 'tw-mb-16' }}">
        {!! rank_math_the_breadcrumbs() !!}
    </div>

    <style>
        .rank-math-breadcrumb a {
            color: #b2111d;
        }

        .rank-math-breadcrumb a:hover {
            text-decoration: underline;
        }

    </style>
@endif
