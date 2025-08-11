<x-layouts.app>
	<x-slot name="title">
		{{ $product->name }} - Product Details
	</x-slot>

	<div class="container py-8">
		<div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
			<!-- Product Images -->
			<div class="space-y-4">
				<div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
					<img
						src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1770&q=80"
						alt="{{ $product->name }}" class="h-full w-full object-cover">
				</div>
			</div>

			<!-- Product Info -->
			<div class="space-y-6">
				<div>
					<h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
					<div class="mt-2 flex items-center space-x-4">
						<span class="text-primary-600 text-2xl font-semibold">
							{{ $product->base_price }}
						</span>
						@if ($product->compare_price)
							<span class="text-lg text-gray-500 line-through">
								{{-- @money($product->compare_price) --}}
							</span>
						@endif
					</div>
				</div>

				<!-- Product Configurator -->
				<livewire:product.configurator :$product :productAttributes="$attributes" />

			</div>
		</div>

		@if ($product->description)
			<div class="mt-12">
				<div class="border-t border-gray-200 pt-8">
					<h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
					<div class="prose prose-sm max-w-none text-gray-600">
						{!! $product->description !!}
					</div>
				</div>
			</div>
		@endif

	</div>
</x-layouts.app>
