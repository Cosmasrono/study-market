@extends('layouts.app')

@section('title', 'Inzoberi School Professionals - Courses & Resources')

@section('content')
<div class="container mx-auto px-4">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-6">Inzoberi School Professionals</h1>
        <p class="text-xl text-gray-600 max-w-4xl mx-auto mb-6">
            Your partner in overcoming exam hurdles and achieving professional qualifications in CIFA and Insurance exams.
        </p>
        <div class="bg-maroon-50 p-6 rounded-lg shadow-md max-w-4xl mx-auto">
            <p class="text-gray-700 mb-4">
                <strong>Gabriel Inzoberi</strong> - With a passion for academia and hundreds of students coached, I've developed a methodical approach to tackle the two primary issues for unsuccessful students: memory retention and difficulty with complex material.
            </p>
            <a href="https://www.linkedin.com/in/fa-gabriel-inzoberi-bsc-aiik-cifa-yipp-b58288b5/" 
               target="_blank" 
               class="inline-block bg-maroon-600 text-white px-6 py-2 rounded hover:bg-maroon-700">
                View LinkedIn Profile
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="flex justify-center space-x-4">
            <button id="cifaTab" class="px-4 py-2 bg-maroon-600 text-white rounded hover:bg-maroon-700 focus:outline-none focus:ring-2 focus:ring-maroon-500">CIFA Program</button>
            <button id="insuranceTab" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Insurance Exams</button>
            <button id="pricingTab" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Pricing Plans</button>
        </div>
    </div>

    <!-- CIFA Program Section -->
    <div id="cifaSection">
        <div class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-3xl font-semibold mb-6">Certified Investment and Financial Analyst (CIFA)</h2>
            <p class="text-gray-700 mb-6">
                This course is divided into three levels, each with a distinct set of subjects designed to equip students with the skills and knowledge needed for a career in investment management and financial analysis.
            </p>
            
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Foundation Level -->
                <div class="bg-maroon-50 p-6 rounded-lg border-l-4 border-maroon-500">
                    <h3 class="text-xl font-semibold mb-4 text-maroon-800">Foundation Level</h3>
                    <p class="text-sm text-gray-600 mb-3">Strong base in financial and economic principles</p>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Financial Accounting</li>
                        <li>• Professional Ethics and Governance</li>
                        <li>• Regulation of Financial Markets</li>
                        <li>• Economics</li>
                        <li>• Quantitative Analysis</li>
                        <li>• Introduction to Finance and Investments</li>
                    </ul>
                </div>

                <!-- Intermediate Level -->
                <div class="bg-maroon-50 p-6 rounded-lg border-l-4 border-maroon-500">
                    <h3 class="text-xl font-semibold mb-4 text-maroon-800">Intermediate Level</h3>
                    <p class="text-sm text-gray-600 mb-3">Practical application of investment tools</p>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Portfolio Management</li>
                        <li>• Financial Statements Analysis</li>
                        <li>• Equity Investments Analysis</li>
                        <li>• Corporate Finance</li>
                        <li>• Public Finance and Taxation</li>
                    </ul>
                </div>

                <!-- Advanced Level -->
                <div class="bg-maroon-50 p-6 rounded-lg border-l-4 border-maroon-500">
                    <h3 class="text-xl font-semibold mb-4 text-maroon-800">Advanced Level</h3>
                    <p class="text-sm text-gray-600 mb-3">Advanced topics for senior roles</p>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>• Leadership and Management</li>
                        <li>• Fixed Income Investments Analysis</li>
                        <li>• Alternative Investments Analysis</li>
                        <li>• Advanced Portfolio Management</li>
                        <li>• Derivatives Analysis</li>
                        <li>• Financial Modelling and Data Analytics</li>
                    </ul>
                </div>
            </div>

            <!-- Fixed Income Focus -->
            <div class="mt-8 bg-maroon-50 p-6 rounded-lg border-l-4 border-maroon-500">
                <h3 class="text-xl font-semibold mb-4 text-maroon-800">Specialized Focus: Fixed Income Investments Analysis</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-2">Fundamentals & Valuation:</h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Bond Fundamentals</li>
                            <li>• Bond Valuation</li>
                            <li>• Yield Measures</li>
                            <li>• Interest Rate Risk</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2">Analysis & Strategies:</h4>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Credit Risk Analysis</li>
                            <li>• Term Structure of Interest Rates</li>
                            <li>• Fixed Income Derivatives</li>
                            <li>• Portfolio Management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insurance Exams Section -->
    <div id="insuranceSection" class="hidden">
        <div class="bg-white p-8 rounded-lg shadow-md mb-8">
            <h2 class="text-3xl font-semibold mb-6">Insurance Qualification Courses</h2>
            <p class="text-gray-700 mb-6">
                Comprehensive preparation for various insurance qualification courses offered by the College of Insurance, designed for both new entrants and experienced professionals.
            </p>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Certificate Courses -->
                <div class="bg-maroon-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4 text-maroon-800">🎓 Certificate Courses</h3>
                    <p class="text-sm text-gray-600 mb-4">Foundational courses ideal for industry newcomers</p>
                    
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Certificate of Proficiency (COP)</h4>
                            <p class="text-sm text-gray-600">Mandatory certification for all insurance agents in Kenya</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Craft Course in Insurance (CCI)</h4>
                            <p class="text-sm text-gray-600">One-year certificate covering insurance and business management</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Executive Certificate of Proficiency (ECOP)</h4>
                            <p class="text-sm text-gray-600">Intensive program for senior staff and executives</p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Courses -->
                <div class="bg-maroon-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4 text-maroon-800">📜 Diploma & Advanced Courses</h3>
                    <p class="text-sm text-gray-600 mb-4">For deepening technical knowledge and career advancement</p>
                    
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Diploma in Insurance</h4>
                            <p class="text-sm text-gray-600">Comprehensive program covering all aspects of insurance</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Loss Adjusting</h4>
                            <p class="text-sm text-gray-600">Specialized course for claims investigation and assessment</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Actuarial Science</h4>
                            <p class="text-sm text-gray-600">Mathematical and statistical methods for risk assessment</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Risk Management</h4>
                            <p class="text-sm text-gray-600">Strategies for identifying and mitigating risks</p>
                        </div>
                        
                        <div class="bg-white p-4 rounded border-l-4 border-maroon-500">
                            <h4 class="font-semibold">Trustee Development Program</h4>
                            <p class="text-sm text-gray-600">For trustees of retirement benefit schemes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div id="pricingSection" class="hidden">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-3xl font-semibold mb-6 text-center">Pricing Plans</h2>
            <p class="text-center text-gray-600 mb-8">Choose the plan that best fits your learning needs</p>
            
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Gold Plan -->
                <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 p-6 rounded-lg text-white shadow-lg transform hover:scale-105 transition-transform">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-2">Gold Plan</h3>
                        <div class="text-3xl font-bold mb-4">Kes 7,000</div>
                        <p class="text-yellow-100 mb-6">Complete package with personalized support</p>
                        <ul class="text-left space-y-2 mb-6">
                            <li>✓ Video lectures</li>
                            <li>✓ Class notes</li>
                            <li>✓ Past papers + Solutions</li>
                            <li>✓ One-on-one review session</li>
                            <li>✓ Block revision</li>
                        </ul>
                        <button class="w-full bg-white text-yellow-600 py-2 rounded font-semibold hover:bg-yellow-50">
                            Choose Gold
                        </button>
                    </div>
                </div>

                <!-- Silver Plan -->
                <div class="bg-gradient-to-br from-gray-400 to-gray-600 p-6 rounded-lg text-white shadow-lg transform hover:scale-105 transition-transform">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-2">Silver Plan</h3>
                        <div class="text-3xl font-bold mb-4">Kes 6,000</div>
                        <p class="text-gray-100 mb-6">Essential learning with revision support</p>
                        <ul class="text-left space-y-2 mb-6">
                            <li>✓ Video lectures</li>
                            <li>✓ Class notes</li>
                            <li>✓ Block revision</li>
                            <li class="text-gray-300">✗ Past papers + Solutions</li>
                            <li class="text-gray-300">✗ One-on-one review</li>
                        </ul>
                        <button class="w-full bg-white text-gray-600 py-2 rounded font-semibold hover:bg-gray-50">
                            Choose Silver
                        </button>
                    </div>
                </div>

                <!-- Bronze Plan -->
                <div class="bg-gradient-to-br from-orange-400 to-orange-600 p-6 rounded-lg text-white shadow-lg transform hover:scale-105 transition-transform">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-2">Bronze Plan</h3>
                        <div class="text-3xl font-bold mb-4">Kes 5,000</div>
                        <p class="text-orange-100 mb-6">Basic learning package</p>
                        <ul class="text-left space-y-2 mb-6">
                            <li>✓ Video lectures</li>
                            <li>✓ Class notes</li>
                            <li class="text-orange-200">✗ Past papers + Solutions</li>
                            <li class="text-orange-200">✗ One-on-one review</li>
                            <li class="text-orange-200">✗ Block revision</li>
                        </ul>
                        <button class="w-full bg-white text-orange-600 py-2 rounded font-semibold hover:bg-orange-50">
                            Choose Bronze
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-4">Additional Options</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded border">
                        <h4 class="font-semibold">Study Package</h4>
                        <p class="text-sm text-gray-600 mb-2">Class Notes + Past papers & Solutions + Block revision</p>
                        <div class="text-lg font-bold text-blue-600">Kes 3,000</div>
                    </div>
                    <div class="bg-white p-4 rounded border">
                        <h4 class="font-semibold">Class Notes Only</h4>
                        <p class="text-sm text-gray-600 mb-2">Comprehensive study materials</p>
                        <div class="text-lg font-bold text-blue-600">Kes 2,000</div>
                    </div>
                    <div class="bg-white p-4 rounded border">
                        <h4 class="font-semibold">Block Review Sessions</h4>
                        <p class="text-sm text-gray-600 mb-2">Intensive revision sessions only</p>
                        <div class="text-lg font-bold text-blue-600">Kes 1,000</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personalized Learning Experience -->
    <div class="mt-12 bg-gradient-to-r from-maroon-600 to-maroon-800 text-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-semibold mb-6 text-center">A Custom, Personalized Learning Experience</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xl font-semibold mb-4">Direct Access & Proactive Support</h3>
                <p class="mb-4">
                    You have direct access to Gabriel for questions and concept guidance. He proactively addresses errors seen early in the process and can easily identify struggling candidates through weekly mock exam reviews.
                </p>
                <p>
                    If you're having difficulty in any area, Gabriel provides personalized one-on-one time to identify and correct issues early on.
                </p>
            </div>
            <div>
                <h3 class="text-xl font-semibold mb-4">Life Happens - We Adapt</h3>
                <p class="mb-4">
                    Life isn't always straightforward, and personal issues can arise unexpectedly. Gabriel encourages candidates to keep him informed about what's happening in their lives.
                </p>
                <p>
                    Together, you'll create strategies for any roadblocks that may arise, ensuring you stay on track to reach your goal of passing CIFA or Insurance exams.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cifaTab = document.getElementById('cifaTab');
        const insuranceTab = document.getElementById('insuranceTab');
        const pricingTab = document.getElementById('pricingTab');
        
        const cifaSection = document.getElementById('cifaSection');
        const insuranceSection = document.getElementById('insuranceSection');
        const pricingSection = document.getElementById('pricingSection');

        function showSection(activeTab, activeSection) {
            // Reset all tabs
            [cifaTab, insuranceTab, pricingTab].forEach(tab => {
                tab.classList.remove('bg-maroon-600', 'text-white');
                tab.classList.add('bg-gray-200', 'text-gray-800');
            });
            
            // Hide all sections
            [cifaSection, insuranceSection, pricingSection].forEach(section => {
                section.classList.add('hidden');
            });
            
            // Activate selected tab and section
            activeTab.classList.remove('bg-gray-200', 'text-gray-800');
            activeTab.classList.add('bg-maroon-600', 'text-white');
            activeSection.classList.remove('hidden');
        }

        cifaTab.addEventListener('click', () => showSection(cifaTab, cifaSection));
        insuranceTab.addEventListener('click', () => showSection(insuranceTab, insuranceSection));
        pricingTab.addEventListener('click', () => showSection(pricingTab, pricingSection));
    });
</script>
@endpush
@endsection