import os
import numpy as np
import cv2
import tensorflow as tf
import pandas as pd
from skimage.metrics import structural_similarity as ssim
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# 🔥 Grad-CAM Function


def compute_gradcam(model, img_array, class_index, conv_layer_name="conv5_block3_out"):
    grad_model = tf.keras.models.Model(
        inputs=model.input,
        outputs=[model.get_layer(conv_layer_name).output, model.output]
    )
    with tf.GradientTape() as tape:
        conv_outputs, predictions = grad_model(img_array)
        loss = predictions[:, class_index]

    grads = tape.gradient(loss, conv_outputs)
    pooled_grads = tf.reduce_mean(grads, axis=(0, 1, 2))
    conv_outputs = conv_outputs.numpy()[0]
    pooled_grads = pooled_grads.numpy()

    for i in range(pooled_grads.shape[-1]):
        conv_outputs[:, :, i] *= pooled_grads[i]

    heatmap = np.mean(conv_outputs, axis=-1)
    heatmap = np.maximum(heatmap, 0)
    heatmap /= np.max(heatmap) + 1e-8
    return heatmap


# 🔄 Overlay Heatmap Function
def overlay_gradcam(original_image, heatmap, alpha=0.4):
    heatmap_resized = cv2.resize(
        heatmap, (original_image.shape[1], original_image.shape[0]))
    heatmap_resized = np.uint8(255 * heatmap_resized)
    heatmap_color = cv2.applyColorMap(heatmap_resized, cv2.COLORMAP_JET)
    return cv2.addWeighted(original_image, alpha, heatmap_color, 1 - alpha, 0)

# 📊 SSIM Comparison


def compare_heatmaps_ssim(hm1, hm2):
    if len(hm1.shape) == 3:
        hm1 = cv2.cvtColor(hm1, cv2.COLOR_BGR2GRAY)
    if len(hm2.shape) == 3:
        hm2 = cv2.cvtColor(hm2, cv2.COLOR_BGR2GRAY)
    hm1 = cv2.resize(hm1, (224, 224))
    hm2 = cv2.resize(hm2, (224, 224))
    # Specify data_range for floating-point images
    data_range = max(hm1.max(), hm2.max()) - min(hm1.min(), hm2.min())
    score, _ = ssim(hm1, hm2, full=True, data_range=data_range)
    return score
