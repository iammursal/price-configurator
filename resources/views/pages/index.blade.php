<x-layouts.app>
    <section>
        <div class="container py-8">
            <x-ui.alerts :type="['success']" />
            
            <header class="text-center">
                <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">Product Collection</h2>
                <p class="mx-auto mt-4 max-w-md text-gray-500">
                    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Itaque praesentium cumque iure
                    dicta incidunt est ipsam, officia dolor fugit natus?
                </p>
            </header>

            <ul class="mt-8 grid gap-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product.card :product="$product" />
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.app>
