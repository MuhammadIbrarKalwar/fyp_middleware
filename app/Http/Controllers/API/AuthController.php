<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * User signup
     */
    public function signup(Request $request)
    {
        try {
            if (!$request->email) {
                return response()->json([
                    'message' => "Email is required",
                ], 403);
            }
            if (!$request->name) {
                return response()->json([
                    'message' => "Name is required",
                ], 403);
            }
            if (!$request->password) {
                return response()->json([
                    'message' => "Password is required",
                ], 403);
            }

            $checkUser = User::where('email', $request->email)->first();

            if ($checkUser) {
                return response()->json([
                    'message' => "User already exists",
                ], 502);
            }

            $password_encrypt = Hash::make($request->password);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password_encrypt,
            ]);

            return response()->json([
                'message' => 'User successfully created!'
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Signup error: ' . $ex->getMessage());
            return response()->json([
                'message' => 'Signup failed: ' . $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(Request $request)
    {
        try {
            if (!$request->email) {
                return response()->json([
                    'message' => "Email is required",
                ], 403);
            }

            if (!$request->password) {
                return response()->json([
                    'message' => "Password is required",
                ], 403);
            }

            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Login successful!',
                    'user' => $user,
                ], 200);
            }

            return response()->json(['message' => 'Invalid credentials'], 401);
        } catch (\Exception $ex) {
            Log::error('Login error: ' . $ex->getMessage());
            return response()->json([
                'message' => "Authentication error",
            ], 500);
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $users = User::all();
            return response()->json(['users' => $users], 200);
        } catch (\Exception $ex) {
            Log::error('Get users error: ' . $ex->getMessage());
            return response()->json([
                'message' => 'Failed to get users',
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            if (!$request->email) {
                return response()->json([
                    'message' => "Email is required",
                ], 403);
            }
            if (!$request->password) {
                return response()->json([
                    'message' => "Password is required",
                ], 403);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => "User not found",
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password updated successfully!'
            ], 200);
        } catch (\Exception $ex) {
            Log::error('Update password error: ' . $ex->getMessage());
            return response()->json([
                'message' => 'Password update failed',
            ], 500);
        }
    }

    /**
     * Get career recommendations based on user profile
     */
    public function getRecommendations(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('=== RECOMMENDATION REQUEST START ===');
            Log::info('Recommendation request received', [
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method(),
                'user_agent' => $request->header('User-Agent'),
                'ip' => $request->ip()
            ]);

            // Comprehensive validation
            $validator = Validator::make($request->all(), [
                'skills' => 'required|array|min:1|max:20',
                'skills.*' => 'string|max:100',
                'interests' => 'required|array|min:1|max:20',
                'interests.*' => 'string|max:100',
                'education' => 'nullable|string|max:200',
                'work_mode' => 'nullable|string|max:50',
                'country' => 'nullable|string|max:100',
                'years_code' => 'nullable|integer|min:0|max:50',
                'work_experience' => 'nullable|numeric|min:0|max:50',
                'main_branch' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prepare comprehensive user data with proper defaults
            $userData = [
                'skills' => $request->input('skills', []),
                'interests' => $request->input('interests', []),
                'main_branch' => $request->input('main_branch', 'Developer'),
                'work_mode' => $request->input('work_mode', 'Hybrid'),
                'education' => $request->input('education', "Bachelor's degree (B.A., B.S., B.Eng., etc.)"),
                'years_code' => (int) $request->input('years_code', 3),
                'country' => $request->input('country', 'USA'),
                'work_experience' => (float) $request->input('work_experience', 2.0),
            ];

            Log::info('Processed user data', ['user_data' => $userData]);

            // Step 1: Enhanced Python detection with comprehensive fallbacks
            $pythonCommand = $this->findPythonCommand();

            if ($pythonCommand === null) {
                Log::error('Python not found - installation required');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Python interpreter not available. Please install Python 3.8+ and ensure it\'s in your system PATH.'
                ], 503);
            }

            Log::info('Python detection successful', ['command' => $pythonCommand]);

            // Step 2: Locate Python script with multiple fallback paths
            $pythonScriptPath = $this->findPythonScript();

            if ($pythonScriptPath === null) {
                Log::error('Python script not found in any location');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Recommendation service temporarily unavailable. Python script not found.'
                ], 503);
            }

            Log::info('Python script located', ['path' => $pythonScriptPath]);

            // Step 3: Locate dataset file
            $datasetPath = $this->findDataset($pythonScriptPath);

            if ($datasetPath === null) {
                Log::error('Dataset file not found');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Recommendation data temporarily unavailable. Dataset not found.'
                ], 503);
            }

            Log::info('Dataset located', ['path' => $datasetPath]);

            // Step 4: Prepare JSON data with proper encoding to avoid command line issues
            $userDataJson = json_encode($userData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON encoding failed', ['error' => json_last_error_msg()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process user data for ML model'
                ], 500);
            }

            // Step 5: Use temporary file approach to avoid JSON escaping issues
            $tempFile = tempnam(sys_get_temp_dir(), 'recommendation_input_');
            if (file_put_contents($tempFile, $userDataJson) === false) {
                Log::error('Failed to create temporary file', ['temp_file' => $tempFile]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to prepare recommendation request'
                ], 500);
            }

            // Step 6: Build secure command with proper escaping
            $command = sprintf(
                '%s %s --file %s 2>&1',
                escapeshellcmd($pythonCommand),
                escapeshellarg($pythonScriptPath),
                escapeshellarg($tempFile)
            );

            Log::info('Executing ML recommendation command', [
                'command' => $command,
                'script_path' => $pythonScriptPath,
                'dataset_path' => $datasetPath,
                'temp_file' => $tempFile,
                'input_data_size' => strlen($userDataJson)
            ]);

            // Step 7: Execute with proper resource limits for ML processing
            set_time_limit(180); // 3 minutes for complex ML operations
            ini_set('memory_limit', '2048M'); // Increased memory for large datasets

            $startTime = microtime(true);
            $output = shell_exec($command);
            $executionTime = microtime(true) - $startTime;

            // Step 8: Clean up temporary file immediately
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            Log::info('Python ML script execution completed', [
                'execution_time' => round($executionTime, 2) . 's',
                'output_length' => strlen($output ?? ''),
                'raw_output' => $output
            ]);

            // Step 9: Validate script output
            if (empty($output)) {
                Log::error('Python script returned empty output', [
                    'command' => $command,
                    'execution_time' => $executionTime
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'ML model failed to generate output. Please try again.'
                ], 500);
            }

            // Step 10: Parse Python output with robust JSON extraction
            $result = $this->parsePythonOutput($output);

            if ($result === null) {
                Log::error('Failed to parse Python output as JSON', [
                    'raw_output' => $output,
                    'output_length' => strlen($output)
                ]);
                // Return fallback recommendations instead of complete failure
                return response()->json([
                    'status' => 'success',
                    'recommendations' => $this->getDefaultRecommendations($userData['skills'])
                ], 200);
            }

            // Step 11: Handle Python script errors
            if (isset($result['status']) && $result['status'] === 'error') {
                Log::error('Python ML script returned error', [
                    'error_message' => $result['message'] ?? 'Unknown ML error',
                    'full_result' => $result
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'ML model error: ' . ($result['message'] ?? 'Unable to process your profile')
                ], 500);
            }

            // Step 12: Validate and process recommendations
            if (!isset($result['recommendations']) || !is_array($result['recommendations'])) {
                Log::warning('Invalid recommendation format from ML model', [
                    'result' => $result,
                    'type' => gettype($result['recommendations'] ?? 'missing')
                ]);
                return response()->json([
                    'status' => 'success',
                    'recommendations' => $this->getDefaultRecommendations($userData['skills'])
                ], 200);
            }

            // Step 13: Clean and validate recommendations
            $recommendations = array_filter(
                array_map('trim', $result['recommendations']),
                function($rec) {
                    return !empty($rec) && strlen($rec) > 2;
                }
            );

            if (empty($recommendations)) {
                Log::warning('No valid recommendations from ML model', [
                    'original_recommendations' => $result['recommendations'],
                    'user_skills' => $userData['skills']
                ]);
                $recommendations = $this->getDefaultRecommendations($userData['skills']);
            }

            // Step 14: Format final recommendations
            $finalRecommendations = array_values(array_slice($recommendations, 0, 5)); // Top 5 max

            // Step 15: Log successful completion
            Log::info('=== RECOMMENDATION REQUEST SUCCESS ===', [
                'user_skills' => $userData['skills'],
                'user_interests' => $userData['interests'],
                'recommendations_count' => count($finalRecommendations),
                'recommendations' => $finalRecommendations,
                'total_execution_time' => round($executionTime, 2) . 's',
                'ml_model_used' => true
            ]);

            // Step 16: Return successful response
            return response()->json([
                'status' => 'success',
                'recommendations' => $finalRecommendations,
                'execution_time' => round($executionTime, 2),
                'model_confidence' => 'high'
            ], 200);

        } catch (\Exception $ex) {
            // Comprehensive error logging
            Log::error('=== RECOMMENDATION REQUEST FAILED ===', [
                'exception_message' => $ex->getMessage(),
                'exception_file' => $ex->getFile(),
                'exception_line' => $ex->getLine(),
                'stack_trace' => $ex->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while generating recommendations. Our team has been notified.'
            ], 500);
        }
    }

    /**
     * Enhanced Python detection with comprehensive OS support
     */
    private function findPythonCommand()
    {
        Log::info('Starting Python detection', ['os_family' => PHP_OS_FAMILY]);

        // Check environment variable first
        $envPython = env('PYTHON_PATH');
        if ($envPython && $this->testPythonCommand($envPython)) {
            Log::info('Python found via environment variable', ['path' => $envPython]);
            return $envPython;
        }

        // Try common command names in order of preference
        $possibleCommands = ['python3', 'python', 'py', 'python3.11', 'python3.10', 'python3.9'];

        foreach ($possibleCommands as $cmd) {
            if ($this->testPythonCommand($cmd)) {
                Log::info('Python found via command', ['command' => $cmd]);
                return $cmd;
            }
        }

        // Try OS-specific absolute paths
        $possiblePaths = $this->getPythonPaths();

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && $this->testPythonCommand($path)) {
                Log::info('Python found via absolute path', ['path' => $path]);
                return $path;
            }
        }

        Log::error('Python not found anywhere', [
            'tried_commands' => $possibleCommands,
            'tried_paths' => array_slice($possiblePaths, 0, 10), // Log first 10 to avoid spam
            'os_family' => PHP_OS_FAMILY
        ]);

        return null;
    }

    /**
     * Get OS-specific Python installation paths
     */
    private function getPythonPaths()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return [
                'C:\\Python311\\python.exe',
                'C:\\Python310\\python.exe',
                'C:\\Python39\\python.exe',
                'C:\\Python312\\python.exe',
                'C:\\Program Files\\Python311\\python.exe',
                'C:\\Program Files\\Python310\\python.exe',
                'C:\\Program Files\\Python39\\python.exe',
                'C:\\Program Files\\Python312\\python.exe',
                'C:\\Program Files (x86)\\Python311\\python.exe',
                'C:\\Program Files (x86)\\Python310\\python.exe',
                'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
                'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe',
            ];
        } else {
            // Unix/Linux/Mac paths
            return [
                '/usr/bin/python3',
                '/usr/local/bin/python3',
                '/opt/homebrew/bin/python3', // Mac M1/M2
                '/usr/bin/python3.11',
                '/usr/bin/python3.10',
                '/usr/bin/python3.9',
                '/usr/local/bin/python3.11',
                '/usr/local/bin/python3.10',
                '/usr/local/opt/python@3.11/bin/python3',
                '/usr/local/opt/python@3.10/bin/python3',
                '/opt/python3/bin/python3',
                '/snap/bin/python3',
                home_path() . '/.pyenv/shims/python3',
            ];
        }
    }

    /**
     * Test if a Python command works and is version 3.8+
     */
    private function testPythonCommand($command)
    {
        try {
            $output = [];
            $returnCode = null;

            // Test version command
            exec(escapeshellcmd($command) . ' --version 2>&1', $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                $versionOutput = implode(' ', $output);

                // Check if it's Python 3.8 or higher
                if (preg_match('/Python (\d+)\.(\d+)/', $versionOutput, $matches)) {
                    $majorVersion = (int)$matches[1];
                    $minorVersion = (int)$matches[2];

                    if ($majorVersion >= 3 && ($majorVersion > 3 || $minorVersion >= 8)) {
                        // Test if required packages are available
                        $testOutput = [];
                        $testCode = null;
                        exec(escapeshellcmd($command) . ' -c "import pandas, sklearn, numpy; print(\'OK\')" 2>&1', $testOutput, $testCode);

                        if ($testCode === 0 && in_array('OK', $testOutput)) {
                            Log::info('Python command validation successful', [
                                'command' => $command,
                                'version' => $versionOutput,
                                'packages' => 'pandas, sklearn, numpy available'
                            ]);
                            return true;
                        } else {
                            Log::warning('Python found but missing required packages', [
                                'command' => $command,
                                'version' => $versionOutput,
                                'package_test_output' => $testOutput
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Python command test failed', [
                'command' => $command,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Find Python script with comprehensive path search
     */
    private function findPythonScript()
    {
        $possiblePaths = [
            base_path('public/python/recommendation.py'),
            base_path('recommendation.py'),
            base_path('python/recommendation.py'),
            public_path('python/recommendation.py'),
            public_path('recommendation.py'),
            storage_path('app/python/recommendation.py'),
            storage_path('app/recommendation.py'),
            storage_path('recommendation.py'),
            base_path('storage/python/recommendation.py'),
            base_path('ml/recommendation.py'),
            base_path('scripts/recommendation.py'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                Log::info('Python script found', ['path' => $path, 'size' => filesize($path) . ' bytes']);
                return $path;
            }
        }

        Log::error('Python script not found', ['searched_paths' => $possiblePaths]);
        return null;
    }

    /**
     * Find dataset file with comprehensive search
     */
    private function findDataset($pythonScriptPath)
    {
        $scriptDir = dirname($pythonScriptPath);

        $possiblePaths = [
            $scriptDir . '/df_upsampled.csv',
            base_path('df_upsampled.csv'),
            base_path('public/python/df_upsampled.csv'),
            public_path('python/df_upsampled.csv'),
            public_path('df_upsampled.csv'),
            storage_path('app/df_upsampled.csv'),
            storage_path('df_upsampled.csv'),
            base_path('data/df_upsampled.csv'),
            base_path('datasets/df_upsampled.csv'),
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path) && filesize($path) > 1000) { // At least 1KB
                Log::info('Dataset found', [
                    'path' => $path,
                    'size' => round(filesize($path) / 1024 / 1024, 2) . ' MB'
                ]);
                return $path;
            }
        }

        Log::error('Dataset not found', ['searched_paths' => $possiblePaths]);
        return null;
    }

    /**
     * Parse Python output with robust JSON extraction
     */
    private function parsePythonOutput($output)
    {
        $cleanOutput = trim($output);

        // Handle empty output
        if (empty($cleanOutput)) {
            Log::warning('Empty output from Python script');
            return null;
        }

        // Split into lines and look for JSON
        $lines = explode("\n", $cleanOutput);
        $jsonCandidates = [];

        // Look for lines that could be JSON (start with { or [)
        foreach ($lines as $lineNumber => $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            if ((substr($trimmedLine, 0, 1) === '{' && substr($trimmedLine, -1) === '}') ||
                (substr($trimmedLine, 0, 1) === '[' && substr($trimmedLine, -1) === ']')) {
                $jsonCandidates[] = ['line' => $lineNumber, 'content' => $trimmedLine];
            }
        }

        // Try to parse JSON candidates
        foreach ($jsonCandidates as $candidate) {
            $result = json_decode($candidate['content'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Successfully parsed JSON output', [
                    'line_number' => $candidate['line'],
                    'json_keys' => array_keys($result)
                ]);
                return $result;
            }
        }

        // If no JSON found, try the last non-empty line
        $lastLine = '';
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (!empty(trim($lines[$i]))) {
                $lastLine = trim($lines[$i]);
                break;
            }
        }

        if (!empty($lastLine)) {
            $result = json_decode($lastLine, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Log::info('Parsed JSON from last line', ['line_content' => $lastLine]);
                return $result;
            }
        }

        Log::warning('Failed to parse any JSON from Python output', [
            'output_lines' => count($lines),
            'json_candidates' => count($jsonCandidates),
            'last_json_error' => json_last_error_msg(),
            'sample_lines' => array_slice($lines, 0, 5)
        ]);

        return null;
    }

    /**
     * Generate intelligent default recommendations based on user skills and interests
     */
    private function getDefaultRecommendations($skills)
    {
        $skillsLower = array_map('strtolower', $skills);
        $recommendations = [];
        $scores = [];

        // Define skill categories and their associated roles
        $roleMapping = [
            'Full Stack Developer' => ['javascript', 'react', 'node.js', 'python', 'java', 'html/css', 'express', 'django', 'flask'],
            'Frontend Developer' => ['javascript', 'react', 'vue.js', 'angular', 'html/css', 'typescript', 'next.js'],
            'Backend Developer' => ['python', 'java', 'node.js', 'php', 'go', 'django', 'flask', 'spring boot', 'express'],
            'Mobile Developer' => ['react native', 'flutter', 'kotlin', 'swift', 'dart', 'java'],
            'Data Scientist' => ['python', 'pandas', 'numpy', 'tensorflow', 'scikit-learn', 'pytorch'],
            'DevOps Engineer' => ['docker', 'kubernetes', 'aws', 'google cloud', 'microsoft azure', 'terraform'],
            'Database Developer' => ['sql', 'mysql', 'postgresql', 'mongodb', 'oracle', 'redis'],
            'Cloud Engineer' => ['aws', 'google cloud', 'microsoft azure', 'docker', 'kubernetes'],
            'Web Developer' => ['javascript', 'html/css', 'php', 'python', 'react', 'vue.js']
        ];

        // Calculate scores for each role
        foreach ($roleMapping as $role => $requiredSkills) {
            $matchCount = count(array_intersect($skillsLower, array_map('strtolower', $requiredSkills)));
            if ($matchCount > 0) {
                $scores[$role] = $matchCount;
            }
        }

        // Sort by score and get top recommendations
        arsort($scores);
        $recommendations = array_keys(array_slice($scores, 0, 3, true));

        // If no matches found, provide general recommendations
        if (empty($recommendations)) {
            $recommendations = [
                'Software Developer',
                'Web Developer',
                'Application Developer'
            ];
        }

        Log::info('Generated default recommendations', [
            'input_skills' => $skills,
            'scores' => $scores,
            'final_recommendations' => $recommendations
        ]);

        return $recommendations;
    }
}

// Helper function for home path (if not already defined)
if (!function_exists('home_path')) {
    function home_path() {
        return $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/home/' . get_current_user();
    }
}
