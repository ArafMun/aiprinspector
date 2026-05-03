<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - AI PR Inspector</title>
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <a href="/" class="text-xl font-bold text-gray-900 hover:text-gray-700">AI PR Inspector</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Terms of Service</h1>

                <div class="prose prose-indigo max-w-none">
                    <p class="text-gray-600 mb-6">Last updated: {{ date('F j, Y') }}</p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Agreement to Terms</h2>
                    <p class="text-gray-600 mb-6">
                        By accessing and using AI Reviewer, you accept and agree to be bound by the terms and
                        provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Description of Service</h2>
                    <p class="text-gray-600 mb-6">
                        AI Reviewer is an AI-powered code review service that analyzes source code, provides
                        recommendations, and helps developers improve code quality. The service integrates with
                        version control systems to provide automated code reviews.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">User Accounts</h2>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Registration</h3>
                    <p class="text-gray-600 mb-6">
                        To access certain features of the service, you must register for an account. You agree to:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Provide accurate, current, and complete information</li>
                        <li>Maintain and update your account information</li>
                        <li>Keep your password secure and confidential</li>
                        <li>Accept responsibility for all activities under your account</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Account Termination</h3>
                    <p class="text-gray-600 mb-6">
                        We reserve the right to suspend or terminate your account at any time for violations
                        of these terms or for any other reason we deem appropriate.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Acceptable Use</h2>
                    <p class="text-gray-600 mb-6">You agree to use our service only for lawful purposes. You are prohibited from:</p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Using the service for any illegal or unauthorized purpose</li>
                        <li>Submitting malicious or harmful code for analysis</li>
                        <li>Attempting to gain unauthorized access to our systems</li>
                        <li>Interfering with or disrupting the service</li>
                        <li>Violating any applicable laws or regulations</li>
                        <li>Infringing on intellectual property rights</li>
                        <li>Submitting excessive requests that burden our infrastructure</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Code and Data Ownership</h2>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Your Content</h3>
                    <p class="text-gray-600 mb-6">
                        You retain ownership of all code and content you submit to our service. We do not claim
                        any ownership rights to your code or intellectual property.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Service Usage Rights</h3>
                    <p class="text-gray-600 mb-6">
                        By submitting code for review, you grant us a limited, non-exclusive license to:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Process and analyze your code using our AI systems</li>
                        <li>Store code temporarily for service functionality</li>
                        <li>Generate review results and recommendations</li>
                        <li>Use anonymized data to improve our services</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Privacy and Data Protection</h2>
                    <p class="text-gray-600 mb-6">
                        Your privacy is important to us. Our collection and use of personal information is governed
                        by our Privacy Policy, which is incorporated into these Terms of Service.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Intellectual Property</h2>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Service Content</h3>
                    <p class="text-gray-600 mb-6">
                        The service, including its software, text, graphics, and overall design, is protected by
                        copyright, trademark, and other intellectual property laws.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Generated Reviews</h3>
                    <p class="text-gray-600 mb-6">
                        Code reviews and recommendations generated by our service are provided for your use.
                        You may use these results in accordance with applicable laws and your licensing obligations.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Service Availability</h2>
                    <p class="text-gray-600 mb-6">
                        We strive to maintain high availability but do not guarantee uninterrupted service.
                        The service may be temporarily unavailable for maintenance, updates, or other reasons.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Disclaimer of Warranties</h2>
                    <p class="text-gray-600 mb-6">
                        Our service is provided "as is" without warranties of any kind, either express or implied.
                        We disclaim all warranties, including but not limited to:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Accuracy or reliability of code review results</li>
                        <li>Fitness for a particular purpose</li>
                        <li>Non-infringement of intellectual property rights</li>
                        <li>Merchantability</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Limitation of Liability</h2>
                    <p class="text-gray-600 mb-6">
                        To the maximum extent permitted by law, AI Reviewer shall not be liable for any indirect,
                        incidental, special, or consequential damages resulting from your use of the service.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Indemnification</h2>
                    <p class="text-gray-600 mb-6">
                        You agree to indemnify and hold AI Reviewer harmless from any claims, damages, or expenses
                        arising from your use of the service or violation of these terms.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Third-Party Services</h2>
                    <p class="text-gray-600 mb-6">
                        Our service may integrate with third-party platforms and services. Your use of such services
                        is governed by their respective terms of service and privacy policies.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Service Modifications</h2>
                    <p class="text-gray-600 mb-6">
                        We reserve the right to modify, suspend, or discontinue the service at any time without
                        prior notice. We are not liable to you or any third party for any modification, suspension,
                        or discontinuation of the service.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Termination</h2>
                    <p class="text-gray-600 mb-6">
                        We may terminate or suspend your account immediately, without prior notice or liability,
                        for any reason, including if you breach the terms.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Governing Law</h2>
                    <p class="text-gray-600 mb-6">
                        These terms shall be governed by and construed in accordance with the laws of the jurisdiction
                        in which AI Reviewer operates, without regard to conflict of law provisions.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Changes to Terms</h2>
                    <p class="text-gray-600 mb-6">
                        We reserve the right to modify these terms at any time. Changes will be effective immediately
                        upon posting. Your continued use of the service constitutes acceptance of any changes.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Contact Information</h2>
                    <p class="text-gray-600 mb-6">
                        If you have any questions about these Terms of Service, please contact us:
                    </p>
                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <p class="text-gray-600">Email: arafatuddin.work@gmail.com</p>
                        <p class="text-gray-600">Phone: +8801674847321</p>
                        <p class="text-gray-600">Address: 791, B-Block, Silimur CDA R/A, Foujdarhat, Sitakunda, Chattogram, 4317, Bangladesh</p>
                        <p class="text-gray-600">LinkedIn: <a href="https://www.linkedin.com/in/arafatuddinwork/" target="_blank" class="text-indigo-600 hover:text-indigo-700">linkedin.com/in/arafatuddinwork</a></p>
                        <p class="text-gray-600">GitHub: <a href="https://github.com/ArafMun" target="_blank" class="text-indigo-600 hover:text-indigo-700">github.com/ArafMun</a></p>
                    </div>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Effective Date</h2>
                    <p class="text-gray-600 mb-6">
                        These Terms of Service are effective as of {{ date('F j, Y') }} and will continue
                        until terminated by either party.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-500">
                    © {{ date('Y') }} AI Reviewer. All rights reserved.
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
</body>
</html>
