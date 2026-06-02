<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - AI PR Inspector</title>
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
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>

                <div class="prose prose-indigo max-w-none">
                    <p class="text-gray-600 mb-6">Last updated: {{ date('F j, Y') }}</p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Introduction</h2>
                    <p class="text-gray-600 mb-6">
                        Welcome to AI PR Inspector. We respect your privacy and are committed to protecting your personal data.
                        This privacy policy explains how we collect, use, and protect your information when you use our
                        AI-powered code review service.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Information We Collect</h2>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Code and Repository Data</h3>
                    <p class="text-gray-600 mb-6">
                        We collect code snippets and repository information that you submit for review. This includes:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Source code files and patches</li>
                        <li>Repository metadata (names, URLs, branch information)</li>
                        <li>Pull request information</li>
                        <li>User account information associated with repositories</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Account Information</h3>
                    <p class="text-gray-600 mb-6">
                        When you create an account, we collect:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Name and email address</li>
                        <li>Authentication credentials</li>
                        <li>User roles and permissions</li>
                        <li>Usage patterns and preferences</li>
                    </ul>

                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Technical Data</h3>
                    <p class="text-gray-600 mb-6">
                        We automatically collect certain technical information:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>IP addresses and device information</li>
                        <li>Browser and operating system details</li>
                        <li>Usage metrics and performance data</li>
                        <li>Log files and error reports</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">How We Use Your Information</h2>
                    <p class="text-gray-600 mb-6">We use your information to:</p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Provide AI-powered code review services</li>
                        <li>Process and analyze code submissions</li>
                        <li>Generate code review recommendations</li>
                        <li>Maintain and improve our services</li>
                        <li>Ensure security and prevent abuse</li>
                        <li>Communicate with you about our services</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Data Security</h2>
                    <p class="text-gray-600 mb-6">
                        We implement appropriate security measures to protect your information:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Encryption of data in transit and at rest</li>
                        <li>Regular security audits and updates</li>
                        <li>Access controls and authentication systems</li>
                        <li>Secure coding practices and vulnerability testing</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Data Retention</h2>
                    <p class="text-gray-600 mb-6">
                        We retain your information only as long as necessary for the purposes outlined in this policy.
                        Code review data is typically retained for 30 days unless you choose to save it longer.
                        Account information is retained until you delete your account.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Third-Party Services</h2>
                    <p class="text-gray-600 mb-6">
                        We may use third-party services to help us provide our services, including:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>AI service providers for code analysis</li>
                        <li>Version control platforms for repository integration</li>
                        <li>Analytics services for usage monitoring</li>
                    </ul>
                    <p class="text-gray-600 mb-6">
                        These providers have access to only the data necessary to perform their functions and are
                        contractually obligated to protect your information.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Your Rights</h2>
                    <p class="text-gray-600 mb-6">
                        You have the right to:
                    </p>
                    <ul class="list-disc pl-6 text-gray-600 mb-6">
                        <li>Access your personal information</li>
                        <li>Correct inaccurate information</li>
                        <li>Delete your account and associated data</li>
                        <li>Opt out of certain data processing activities</li>
                        <li>Request a copy of your data</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Children's Privacy</h2>
                    <p class="text-gray-600 mb-6">
                        Our service is not intended for children under 13. We do not knowingly collect
                        personal information from children under 13.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">International Data Transfers</h2>
                    <p class="text-gray-600 mb-6">
                        Your information may be transferred to and processed in countries other than your own.
                        We ensure such transfers comply with applicable data protection laws.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Changes to This Policy</h2>
                    <p class="text-gray-600 mb-6">
                        We may update this privacy policy from time to time. We will notify you of any changes
                        by posting the new policy on this page and updating the "Last updated" date.
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Contact Us</h2>
                    <p class="text-gray-600 mb-6">
                        If you have any questions about this Privacy Policy, please contact us at:
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
                        This Privacy Policy is effective as of {{ date('F j, Y') }} and will remain in effect
                        except as revised in accordance with this policy.
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
</body>
</html>
