<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="text-sm text-gray-500">
                © {{ date('Y') }} AI PR Inspector. All rights reserved.
            </div>
            <div class="flex space-x-6 mt-4 md:mt-0">
                <a href="{{ route('privacy') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    Privacy Policy
                </a>
                <a href="{{ route('terms') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    Terms of Service
                </a>
                <a href="{{ route('contact') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    Contact
                </a>
            </div>
        </div>
    </div>
</footer>
