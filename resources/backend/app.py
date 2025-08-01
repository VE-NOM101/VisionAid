from flask import Flask, request, jsonify
from flask_cors import CORS
import tensorflow as tf
import numpy as np
import os
from preprocess import preprocess_image  # Import your preprocess function
# import gradCam & ssim function
from gradCam import compute_gradcam, overlay_gradcam, compare_heatmaps_ssim
from tensorflow.keras.preprocessing.image import ImageDataGenerator
import pandas as pd
import shutil
import uuid
import cv2
# Initialize Flask app
app = Flask(__name__)
CORS(app)

# Load the pre-trained model
model = tf.keras.models.load_model("model_resnet50_v4.keras")

# Constants
IMG_SIZE = 224  # Target image size for the model
PROCESSED_FOLDER = "./processed"  # Folder for preprocessed images
# Absolute paths to Laravel's public directories
LARAVEL_HEATMAP_DIR = os.path.abspath(os.path.join(
    os.path.dirname(__file__), "../../public/heatmap"))
LARAVEL_OVERLAY_DIR = os.path.abspath(os.path.join(
    os.path.dirname(__file__), "../../public/images/overlay_images"))


def clean_up_folder(folder_path):
    """
    Removes all files and subfolders in the specified folder path.
    """
    if os.path.exists(folder_path):
        shutil.rmtree(folder_path)
    os.makedirs(folder_path, exist_ok=True)


def perform_prediction(image_path):
    clean_up_folder(PROCESSED_FOLDER)
    normalized_path = os.path.normpath(image_path)

    if not os.path.exists(normalized_path):
        raise FileNotFoundError(f'Image not found at {normalized_path}')

    image_name = os.path.basename(normalized_path)
    preprocess_image(file=image_name.split('.')[0], source=os.path.dirname(
        normalized_path), destination=PROCESSED_FOLDER)

    df = pd.DataFrame({'file_name': [image_name]})
    complete_datagen = ImageDataGenerator(rescale=1.0 / 255)
    complete_generator = complete_datagen.flow_from_dataframe(
        dataframe=df,
        directory=PROCESSED_FOLDER,
        x_col="file_name",
        target_size=(IMG_SIZE, IMG_SIZE),
        batch_size=1,
        shuffle=False,
        class_mode=None
    )

    batch = next(complete_generator)
    batch_tensor = tf.convert_to_tensor(batch, dtype=tf.float32)
    prediction = model.predict(batch_tensor)
    result = int(np.argmax(prediction[0]))
    prediction_list = prediction[0].tolist()

    return {
        'prediction': result,
        'prediction_list': prediction_list,
        'preprocessed_image': os.path.join(PROCESSED_FOLDER, image_name),
        'img_array': batch_tensor
    }


# Routes

@app.route('/predict', methods=['POST'])
def predict():
    try:
        if 'image_path' not in request.form:
            return jsonify({'error': 'No image path provided'}), 400

        image_path = request.form['image_path']
        result = perform_prediction(image_path)

        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/gradCam', methods=['POST'])
def gradCam():
    try:
        if 'image' not in request.files:
            return jsonify({'success': False, 'error': 'No image file provided'}), 400

        image_file = request.files['image']
        os.makedirs('./temp', exist_ok=True)
        os.makedirs(LARAVEL_HEATMAP_DIR, exist_ok=True)
        os.makedirs(LARAVEL_OVERLAY_DIR, exist_ok=True)

        image_path = f"./temp/{image_file.filename}"
        image_file.save(image_path)

        result = perform_prediction(image_path)

        # Get the image array (batch_tensor) from the result
        img_array = result['img_array']
        heatmap = compute_gradcam(
            model, img_array, result['prediction'], "conv5_block3_out")

        # Save heatmap as .npy
        unique_id = uuid.uuid4().hex
        heatmap_filename = f"{unique_id}.npy"
        heatmap_path = os.path.join(LARAVEL_HEATMAP_DIR, heatmap_filename)
        np.save(heatmap_path, heatmap)

        # Load original image (after preprocessing or before)
        original_image = cv2.imread(result['preprocessed_image'])
        overlay_img = overlay_gradcam(original_image, heatmap)

        # Save overlay image
        overlay_filename = f"{unique_id}.png"
        overlay_path = os.path.join(LARAVEL_OVERLAY_DIR, overlay_filename)
        cv2.imwrite(overlay_path, overlay_img)

        # Clean up temporary uploaded image
        if os.path.exists(image_path):
            os.remove(image_path)

        return jsonify({
            'success': True,
            'prediction': result['prediction'],
            'heatmap_file': unique_id
        })

    except Exception as e:
        print('Error in /gradCam:', str(e))
        return jsonify({'error': str(e)}), 500


@app.route('/compare-heatmap-ssim', methods=['POST'])
def compare_heatmaps():
    try:
        data = request.json
        hm1 = data.get('hm1')
        hm2 = data.get('hm2')

        if not hm1 or not hm2:
            return jsonify({'success': False, 'error': 'Both heatmap names are required'}), 400

        # ✅ Load .npy files
        hm1_path = os.path.join(LARAVEL_HEATMAP_DIR, f'{hm1}.npy')
        hm2_path = os.path.join(LARAVEL_HEATMAP_DIR, f'{hm2}.npy')

        if not os.path.exists(hm1_path) or not os.path.exists(hm2_path):
            return jsonify({'success': False, 'error': 'One or both files not found'}), 404

        heatmap1 = np.load(hm1_path)
        heatmap2 = np.load(hm2_path)

        # ✅ Compare using SSIM
        score = compare_heatmaps_ssim(heatmap1, heatmap2)

        return jsonify({
            'success': True,
            'ssim_score': round(score, 4),
            'message': 'SSIM Score',
        })
    except Exception as e:
        print('Error in /gradCam:', str(e))
        return jsonify({'error': str(e)}), 500


# Main APP
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
