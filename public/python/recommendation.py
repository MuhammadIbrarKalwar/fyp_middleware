#!/usr/bin/env python3
"""
Career Recommendation System - ML Engine
Complete solution addressing all integration issues
Compatible with the exact same logic as Jupyter notebook
"""
import sys
import json
import pandas as pd
import numpy as np
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.model_selection import train_test_split
import os
import argparse
import warnings
import traceback
from datetime import datetime

# Suppress sklearn warnings for cleaner output
warnings.filterwarnings('ignore')

def log_info(message, data=None):
    """Log information for debugging"""
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    if data:
        print(f"[{timestamp}] INFO: {message} - {data}", file=sys.stderr)
    else:
        print(f"[{timestamp}] INFO: {message}", file=sys.stderr)

def load_dataset():
    """Load the upsampled dataset with comprehensive path search"""
    try:
        # Get the directory where this script is located
        script_dir = os.path.dirname(os.path.abspath(__file__))

        # Try multiple possible locations for the dataset
        possible_paths = [
            os.path.join(script_dir, 'df_upsampled.csv'),
            os.path.join(os.path.dirname(script_dir), 'df_upsampled.csv'),
            os.path.join(script_dir, '..', 'df_upsampled.csv'),
            os.path.join(script_dir, '..', '..', 'df_upsampled.csv'),
            'df_upsampled.csv',
            '../df_upsampled.csv',
            '../../df_upsampled.csv',
            os.path.expanduser('~/df_upsampled.csv'),
        ]

        dataset_path = None
        for path in possible_paths:
            abs_path = os.path.abspath(path)
            if os.path.exists(abs_path) and os.path.getsize(abs_path) > 1000:  # At least 1KB
                dataset_path = abs_path
                break

        if dataset_path is None:
            raise FileNotFoundError(f"Dataset not found in any of these locations: {possible_paths}")

        log_info(f"Loading dataset from: {dataset_path}")

        # Load dataset with error handling
        df_upsampled = pd.read_csv(dataset_path)

        if df_upsampled.empty:
            raise ValueError("Dataset is empty")

        if 'current_role' not in df_upsampled.columns:
            raise ValueError("Dataset missing 'current_role' column")

        log_info(f"Dataset loaded successfully", {
            'shape': df_upsampled.shape,
            'columns': len(df_upsampled.columns),
            'target_column': 'current_role' in df_upsampled.columns
        })

        return df_upsampled

    except Exception as e:
        raise Exception(f"Failed to load dataset: {str(e)}")

def prepare_model_data(df_upsampled):
    """Prepare data exactly as in the Jupyter notebook"""
    try:
        log_info("Preparing model data (same as Jupyter notebook)")

        # Same as notebook: exclude response_id and current_role from input columns
        input_columns = [col for col in df_upsampled.columns if col not in ['response_id', 'current_role']]
        target_column = 'current_role'

        log_info(f"Input columns: {len(input_columns)}, Target: {target_column}")

        # Prepare the data
        X = df_upsampled[input_columns].copy()
        y = df_upsampled[target_column].copy()

        # Encode the target variable
        le_target = LabelEncoder()
        y_encoded = le_target.fit_transform(y)

        log_info(f"Target classes: {len(le_target.classes_)}")

        # Encode categorical input features (same as notebook)
        label_encoders = {}
        X_encoded = X.copy()

        categorical_columns = X.select_dtypes(include=['object']).columns
        log_info(f"Categorical columns to encode: {len(categorical_columns)}")

        for col in categorical_columns:
            le = LabelEncoder()
            X_encoded[col] = le.fit_transform(X[col])
            label_encoders[col] = le

        # Split the data (same as notebook)
        X_train, X_test, y_train, y_test = train_test_split(
            X_encoded, y_encoded, test_size=0.2, random_state=42
        )

        log_info("Model data preparation completed", {
            'X_shape': X_encoded.shape,
            'y_shape': y_encoded.shape,
            'train_size': X_train.shape[0],
            'test_size': X_test.shape[0]
        })

        return X_encoded, y_encoded, le_target, label_encoders, input_columns

    except Exception as e:
        raise Exception(f"Failed to prepare model data: {str(e)}")

def create_input_features_template():
    """Create template with all possible input features (same structure as notebook)"""

    # This is the exact same structure as the hardcoded input in the notebook
    input_features = {
        'Unnamed: 0': 0,
        'main_branch': 'Developer',
        'work_mode': 'Hybrid',
        'education': "Bachelor's degree (B.A., B.S., B.Eng., etc.)",
        'years_code': 5,
        'country': 'USA',
        'work_experience': 2.0,

        # Database technologies - interests
        'BigQuery_interest': 0, 'Cassandra_interest': 0, 'Cloud Firestore_interest': 0,
        'Cosmos DB_interest': 0, 'Dynamodb_interest': 0, 'Elasticsearch_interest': 0,
        'Firebase Realtime Database_interest': 0, 'MariaDB_interest': 0, 'Microsoft Access_interest': 0,
        'Microsoft SQL Server_interest': 0, 'MongoDB_interest': 0, 'MySQL_interest': 0,
        'Oracle_interest': 0, 'PostgreSQL_interest': 0, 'Redis_interest': 0,
        'SQLite_interest': 0, 'Snowflake_interest': 0, 'Supabase_interest': 0,

        # Cloud platforms - interests
        'Amazon Web Services (AWS)_interest': 0, 'Cloudflare_interest': 0, 'Digital Ocean_interest': 0,
        'Firebase_interest': 0, 'Fly.io_interest': 0, 'Google Cloud_interest': 0,
        'Heroku_interest': 0, 'Hetzner_interest': 0, 'Linode, now Akamai_interest': 0,
        'Microsoft Azure_interest': 0, 'Netlify_interest': 0, 'Vercel_interest': 0,

        # Programming languages - interests
        'Assembly_interest': 0, 'Bash/Shell (all shells)_interest': 0, 'C_interest': 0,
        'C#_interest': 0, 'C++_interest': 0, 'Clojure_interest': 0, 'Dart_interest': 0,
        'Delphi_interest': 0, 'Elixir_interest': 0, 'Go_interest': 0, 'HTML/CSS_interest': 0,
        'Haskell_interest': 0, 'Java_interest': 0, 'JavaScript_interest': 0, 'Julia_interest': 0,
        'Kotlin_interest': 0, 'Lua_interest': 0, 'PHP_interest': 0, 'PowerShell_interest': 0,
        'Python_interest': 0, 'R_interest': 0, 'Ruby_interest': 0, 'Rust_interest': 0,
        'SQL_interest': 0, 'Scala_interest': 0, 'Swift_interest': 0, 'TypeScript_interest': 0,
        'Zig_interest': 0,

        # Frameworks and technologies - interests
        '.NET (5+) _interest': 0, 'ASP.NET CORE_interest': 0, 'Angular_interest': 0,
        'AngularJS_interest': 0, 'Apache Kafka_interest': 0, 'Apache Spark_interest': 0,
        'Blazor_interest': 0, 'CUDA_interest': 0, 'Deno_interest': 0, 'Django_interest': 0,
        'Electron_interest': 0, 'Express_interest': 0, 'FastAPI_interest': 0, 'Flask_interest': 0,
        'Flutter_interest': 0, 'Hadoop_interest': 0, 'Hugging Face Transformers_interest': 0,
        'Keras_interest': 0, 'Laravel_interest': 0, 'NestJS_interest': 0, 'Next.js_interest': 0,
        'Node.js_interest': 0, 'NumPy_interest': 0, 'Nuxt.js_interest': 0, 'OpenGL_interest': 0,
        'Opencv_interest': 0, 'Pandas_interest': 0, 'Phoenix_interest': 0, 'Qt_interest': 0,
        'Qwik_interest': 0, 'RabbitMQ_interest': 0, 'React_interest': 0, 'React Native_interest': 0,
        'Remix_interest': 0, 'Ruby on Rails_interest': 0, 'Scikit-Learn_interest': 0,
        'Solid.js_interest': 0, 'Spring Boot_interest': 0, 'Spring Framework_interest': 0,
        'Svelte_interest': 0, 'SwiftUI_interest': 0, 'Tauri_interest': 0, 'TensorFlow_interest': 0,
        'Torch/PyTorch_interest': 0, 'Vue.js_interest': 0, 'WordPress_interest': 0, 'jQuery_interest': 0,

        # Development tools - interests
        'APT_interest': 0, 'Ansible_interest': 0, 'Bun_interest': 0, 'CMake_interest': 0,
        'Cargo_interest': 0, 'Chocolatey_interest': 0, 'Composer_interest': 0, 'Docker_interest': 0,
        'GNU GCC_interest': 0, 'Godot_interest': 0, 'Gradle_interest': 0, 'Homebrew_interest': 0,
        'Kubernetes_interest': 0, "LLVM's Clang_interest": 0, 'MSBuild_interest': 0,
        'MSVC_interest': 0, 'Make_interest': 0, 'Maven (build tool)_interest': 0,
        'Ninja_interest': 0, 'Nix_interest': 0, 'NuGet_interest': 0, 'Pacman_interest': 0,
        'Pip_interest': 0, 'Podman_interest': 0, 'Terraform_interest': 0, 'Unity 3D_interest': 0,
        'Unreal Engine_interest': 0, 'Visual Studio Solution_interest': 0, 'Vite_interest': 0,
        'Webpack_interest': 0, 'Yarn_interest': 0, 'npm_interest': 0, 'pnpm_interest': 0,

        # All corresponding skill fields (same as interests but with _skill suffix)
        'BigQuery_skill': 0, 'Cassandra_skill': 0, 'Cloud Firestore_skill': 0,
        'Cosmos DB_skill': 0, 'Dynamodb_skill': 0, 'Elasticsearch_skill': 0,
        'Firebase Realtime Database_skill': 0, 'MariaDB_skill': 0, 'Microsoft Access_skill': 0,
        'Microsoft SQL Server_skill': 0, 'MongoDB_skill': 0, 'MySQL_skill': 0,
        'Oracle_skill': 0, 'PostgreSQL_skill': 0, 'Redis_skill': 0, 'SQLite_skill': 0,
        'Snowflake_skill': 0, 'Supabase_skill': 0, 'Amazon Web Services (AWS)_skill': 0,
        'Cloudflare_skill': 0, 'Digital Ocean_skill': 0, 'Firebase_skill': 0,
        'Fly.io_skill': 0, 'Google Cloud_skill': 0, 'Heroku_skill': 0, 'Hetzner_skill': 0,
        'Linode, now Akamai_skill': 0, 'Microsoft Azure_skill': 0, 'Netlify_skill': 0,
        'Vercel_skill': 0, 'Assembly_skill': 0, 'Bash/Shell (all shells)_skill': 0,
        'C_skill': 0, 'C#_skill': 0, 'C++_skill': 0, 'Clojure_skill': 0, 'Dart_skill': 0,
        'Delphi_skill': 0, 'Elixir_skill': 0, 'Go_skill': 0, 'HTML/CSS_skill': 0,
        'Haskell_skill': 0, 'Java_skill': 0, 'JavaScript_skill': 0, 'Julia_skill': 0,
        'Kotlin_skill': 0, 'Lua_skill': 0, 'PHP_skill': 0, 'PowerShell_skill': 0,
        'Python_skill': 0, 'R_skill': 0, 'Ruby_skill': 0, 'Rust_skill': 0, 'SQL_skill': 0,
        'Scala_skill': 0, 'Swift_skill': 0, 'TypeScript_skill': 0, 'Zig_skill': 0,
        '.NET (5+) _skill': 0, 'ASP.NET CORE_skill': 0, 'Angular_skill': 0,
        'AngularJS_skill': 0, 'Apache Kafka_skill': 0, 'Apache Spark_skill': 0,
        'Blazor_skill': 0, 'CUDA_skill': 0, 'Deno_skill': 0, 'Django_skill': 0,
        'Electron_skill': 0, 'Express_skill': 0, 'FastAPI_skill': 0, 'Flask_skill': 0,
        'Flutter_skill': 0, 'Hadoop_skill': 0, 'Hugging Face Transformers_skill': 0,
        'Keras_skill': 0, 'Laravel_skill': 0, 'NestJS_skill': 0, 'Next.js_skill': 0,
        'Node.js_skill': 0, 'NumPy_skill': 0, 'Nuxt.js_skill': 0, 'OpenGL_skill': 0,
        'Opencv_skill': 0, 'Pandas_skill': 0, 'Phoenix_skill': 0, 'Qt_skill': 0,
        'Qwik_skill': 0, 'RabbitMQ_skill': 0, 'React_skill': 0, 'React Native_skill': 0,
        'Remix_skill': 0, 'Ruby on Rails_skill': 0, 'Scikit-Learn_skill': 0,
        'Solid.js_skill': 0, 'Spring Boot_skill': 0, 'Spring Framework_skill': 0,
        'Svelte_skill': 0, 'SwiftUI_skill': 0, 'Tauri_skill': 0, 'TensorFlow_skill': 0,
        'Torch/PyTorch_skill': 0, 'Vue.js_skill': 0, 'WordPress_skill': 0, 'jQuery_skill': 0,
        'APT_skill': 0, 'Ansible_skill': 0, 'Bun_skill': 0, 'CMake_skill': 0,
        'Cargo_skill': 0, 'Chocolatey_skill': 0, 'Composer_skill': 0, 'Docker_skill': 0,
        'GNU GCC_skill': 0, 'Godot_skill': 0, 'Gradle_skill': 0, 'Homebrew_skill': 0,
        'Kubernetes_skill': 0, "LLVM's Clang_skill": 0, 'MSBuild_skill': 0,
        'MSVC_skill': 0, 'Make_skill': 0, 'Maven (build tool)_skill': 0, 'Ninja_skill': 0,
        'Nix_skill': 0, 'NuGet_skill': 0, 'Pacman_skill': 0, 'Pip_skill': 0,
        'Podman_skill': 0, 'Terraform_skill': 0, 'Unity 3D_skill': 0, 'Unreal Engine_skill': 0,
        'Visual Studio Solution_skill': 0, 'Vite_skill': 0, 'Webpack_skill': 0,
        'Yarn_skill': 0, 'npm_skill': 0, 'pnpm_skill': 0,

        # Employment status columns
        'Employed, full-time': 1.0,
        'Employed, part-time': 0.0,
        'Independent contractor, freelancer, or self-employed': 0.0,
        'Not employed, and not looking for work': 0.0,
        'Not employed, but looking for work': 0.0,
        'Retired': 0.0,
        'Student, full-time': 0.0,
        'Student, part-time': 0.0
    }

    return input_features

def update_input_features_from_user_data(input_features, user_data):
    """Update the template with user-provided data"""
    try:
        log_info("Updating input features with user data")

        # Update basic profile information
        if 'main_branch' in user_data:
            input_features['main_branch'] = str(user_data['main_branch'])
        if 'work_mode' in user_data:
            input_features['work_mode'] = str(user_data['work_mode'])
        if 'education' in user_data:
            input_features['education'] = str(user_data['education'])
        if 'years_code' in user_data:
            input_features['years_code'] = int(user_data['years_code'])
        if 'country' in user_data:
            input_features['country'] = str(user_data['country'])
        if 'work_experience' in user_data:
            input_features['work_experience'] = float(user_data['work_experience'])

        # Handle skills from user input
        skills = user_data.get('skills', [])
        skills_mapped = 0
        for skill in skills:
            skill_key = f"{skill}_skill"
            interest_key = f"{skill}_interest"

            if skill_key in input_features:
                input_features[skill_key] = 1
                skills_mapped += 1
            if interest_key in input_features:
                input_features[interest_key] = 1

        # Handle interests from user input
        interests = user_data.get('interests', [])
        interests_mapped = 0
        for interest in interests:
            # Map common interest names to technical skills
            interest_mapping = {
                'Web': ['HTML/CSS', 'JavaScript', 'React'],
                'AI': ['Python', 'TensorFlow', 'PyTorch'],
                'Full Stack': ['JavaScript', 'React', 'Node.js'],
                'Mobile': ['React Native', 'Flutter'],
                'Cloud': ['AWS', 'Google Cloud', 'Microsoft Azure'],
                'Data Science': ['Python', 'Pandas', 'NumPy'],
                'Backend Development': ['Python', 'Java', 'Node.js'],
                'Frontend Development': ['JavaScript', 'React', 'Vue.js'],
                'DevOps': ['Docker', 'Kubernetes'],
                'Database': ['SQL', 'MySQL', 'PostgreSQL']
            }

            if interest in interest_mapping:
                for mapped_skill in interest_mapping[interest]:
                    skill_key = f"{mapped_skill}_skill"
                    interest_key = f"{mapped_skill}_interest"
                    if skill_key in input_features:
                        input_features[skill_key] = 1
                    if interest_key in input_features:
                        input_features[interest_key] = 1
                        interests_mapped += 1
            else:
                # Direct mapping
                interest_key = f"{interest}_interest"
                if interest_key in input_features:
                    input_features[interest_key] = 1
                    interests_mapped += 1

        log_info("Input features updated", {
            'skills_provided': len(skills),
            'skills_mapped': skills_mapped,
            'interests_provided': len(interests),
            'interests_mapped': interests_mapped
        })

        return input_features

    except Exception as e:
        log_info(f"Error updating input features: {str(e)}")
        raise Exception(f"Failed to update input features: {str(e)}")

def recommend_roles(input_features, X, y_encoded, le_target, label_encoders, input_columns, top_n=3):
    """Same recommendation function as in the Jupyter notebook"""
    try:
        log_info("Starting recommendation process (same logic as Jupyter notebook)")

        # Convert input_features to a DataFrame
        input_df = pd.DataFrame([input_features], columns=input_columns)

        # Encode categorical features in the input
        categorical_cols_processed = 0
        for col in input_df.select_dtypes(include=['object']).columns:
            if col in label_encoders:
                try:
                    input_df[col] = label_encoders[col].transform(input_df[col])
                    categorical_cols_processed += 1
                except ValueError:
                    # Handle unseen categories in the input features
                    input_df[col] = input_df[col].apply(lambda x: -1)
                    categorical_cols_processed += 1

        # Fill missing values with 0 (or another suitable default)
        input_df = input_df.fillna(0)

        # Ensure the input_df columns match X's columns
        input_df = input_df.reindex(columns=X.columns, fill_value=0)

        log_info("Input processing completed", {
            'input_shape': input_df.shape,
            'categorical_cols_processed': categorical_cols_processed,
            'features_count': len(X.columns)
        })

        # Calculate cosine similarity (same as notebook)
        input_vector = input_df.values
        similarities = cosine_similarity(input_vector, X).flatten()

        # Get the indices of the top N similar rows
        top_indices = similarities.argsort()[-top_n*2:][::-1]  # Get more to filter duplicates

        # Retrieve the most similar roles
        recommended_roles = le_target.inverse_transform(y_encoded[top_indices])

        # Remove duplicates while preserving order
        unique_roles = []
        seen_roles = set()

        for role in recommended_roles:
            role_clean = str(role).strip()
            if role_clean not in seen_roles and len(role_clean) > 2:
                unique_roles.append(role_clean)
                seen_roles.add(role_clean)
            if len(unique_roles) >= top_n:
                break

        log_info("Recommendations generated", {
            'total_candidates': len(recommended_roles),
            'unique_recommendations': len(unique_roles),
            'top_similarities': similarities[top_indices[:3]].tolist()
        })

        return unique_roles

    except Exception as e:
        log_info(f"Error in recommendation process: {str(e)}")
        raise Exception(f"Failed to generate recommendations: {str(e)}")

def validate_user_data(user_data):
    """Validate user input data"""
    if not isinstance(user_data, dict):
        raise ValueError("User data must be a dictionary")

    required_fields = ['skills', 'interests']
    for field in required_fields:
        if field not in user_data:
            raise ValueError(f"Missing required field: {field}")
        if not isinstance(user_data[field], list):
            raise ValueError(f"Field '{field}' must be a list")
        if len(user_data[field]) == 0:
            raise ValueError(f"Field '{field}' cannot be empty")

    # Validate data types
    if 'years_code' in user_data:
        try:
            user_data['years_code'] = int(user_data['years_code'])
        except (ValueError, TypeError):
            raise ValueError("years_code must be a number")

    if 'work_experience' in user_data:
        try:
            user_data['work_experience'] = float(user_data['work_experience'])
        except (ValueError, TypeError):
            raise ValueError("work_experience must be a number")

    return user_data

def main():
    """Main function - handles all input methods and processing"""
    try:
        start_time = datetime.now()
        log_info("=== Career Recommendation System Started ===")

        # Parse command line arguments
        parser = argparse.ArgumentParser(description='Career Recommendation ML Engine')
        parser.add_argument('--file', help='JSON file with user data')
        parser.add_argument('user_data', nargs='?', help='JSON string with user data')

        args = parser.parse_args()

        # Get user input from file or command line
        user_data = None
        if args.file:
            log_info(f"Reading user data from file: {args.file}")
            try:
                with open(args.file, 'r', encoding='utf-8') as f:
                    user_data = json.load(f)
            except FileNotFoundError:
                raise ValueError(f"Input file not found: {args.file}")
            except json.JSONDecodeError as e:
                raise ValueError(f"Invalid JSON in file {args.file}: {str(e)}")
        elif args.user_data:
            log_info("Reading user data from command line argument")
            try:
                user_data = json.loads(args.user_data)
            except json.JSONDecodeError as e:
                raise ValueError(f"Invalid JSON in command line argument: {str(e)}")
        else:
            raise ValueError("No user data provided. Use --file <filename> or provide JSON string as argument.")

        # Validate user data
        user_data = validate_user_data(user_data)
        log_info("User data validation successful", user_data)

        # Load and prepare data (same as notebook)
        log_info("Loading ML model and dataset...")
        df_upsampled = load_dataset()
        X, y_encoded, le_target, label_encoders, input_columns = prepare_model_data(df_upsampled)

        # Create input features template
        log_info("Creating input features template...")
        input_features = create_input_features_template()

        # Update with user data
        log_info("Mapping user data to ML model features...")
        input_features = update_input_features_from_user_data(input_features, user_data)

        # Get recommendations (same function as notebook)
        log_info("Generating career recommendations...")
        recommendations = recommend_roles(input_features, X, y_encoded, le_target, label_encoders, input_columns)

        # Calculate execution time
        end_time = datetime.now()
        execution_time = (end_time - start_time).total_seconds()

        # Prepare successful result
        result = {
            "status": "success",
            "recommendations": recommendations,
            "execution_time": round(execution_time, 2),
            "model_info": {
                "dataset_size": df_upsampled.shape[0],
                "features_count": len(input_columns),
                "target_classes": len(le_target.classes_)
            }
        }

        log_info("=== Recommendation Generation Successful ===", {
            'recommendations_count': len(recommendations),
            'execution_time': f"{execution_time:.2f}s"
        })

        # Output final result as clean JSON
        print(json.dumps(result, ensure_ascii=False, separators=(',', ':')))

    except Exception as e:
        # Log detailed error information
        error_details = {
            'error_type': type(e).__name__,
            'error_message': str(e),
            'traceback': traceback.format_exc()
        }
        log_info("=== Recommendation Generation Failed ===", error_details)

        # Output clean error result
        error_result = {
            "status": "error",
            "message": str(e)
        }
        print(json.dumps(error_result, ensure_ascii=False, separators=(',', ':')))
        sys.exit(1)

if __name__ == "__main__":
    main()
