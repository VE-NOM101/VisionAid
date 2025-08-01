import os
import cv2
import numpy as np

IMG_SIZE = 512

def image_resize_save(file, source, destination):
    try:
        input_filepath = os.path.join('./', source, f'{file}.png')
        output_dir = os.path.join('./', destination)
        output_filepath = os.path.join(output_dir, f'{file}.png')
        os.makedirs(output_dir, exist_ok=True)
        img = cv2.imread(input_filepath)
        if img is None:
            print(f"WARNING: Image {input_filepath} not found or couldn't be loaded.")
            return
        resized_img = cv2.resize(img, (IMG_SIZE, IMG_SIZE))
        cv2.imwrite(output_filepath, resized_img)
        print(f"SUCCESS: Resized image saved at {output_filepath}")
    except Exception as e:
        print(f"ERROR: Failed to process {file} due to {e}")

def crop_image_from_gray(img, tol=7):
    if img is None:
        print("WARNING: Received a None image.")
        return None
    try:
        if img.ndim == 2:
            mask = img > tol
            return img[np.ix_(mask.any(1), mask.any(0))]
        elif img.ndim == 3:
            gray_img = cv2.cvtColor(img, cv2.COLOR_RGB2GRAY)
            mask = gray_img > tol
            if not mask.any():
                print("WARNING: Image is too dark, returning the original image.")
                return img
            img1 = img[:, :, 0][np.ix_(mask.any(1), mask.any(0))]
            img2 = img[:, :, 1][np.ix_(mask.any(1), mask.any(0))]
            img3 = img[:, :, 2][np.ix_(mask.any(1), mask.any(0))]
            img = np.stack([img1, img2, img3], axis=-1)
            return img
    except Exception as e:
        print(f"ERROR: Failed to crop image. Reason: {e}")
        return img

def circle_crop(img, sigmaX=30):
    if img is None:
        print("WARNING: Image is None, skipping circle crop.")
        return None
    try:
        img = crop_image_from_gray(img)
        if img is None:
            return None
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        height, width, _ = img.shape
        x, y = int(width / 2), int(height / 2)
        r = np.amin((x, y))
        circle_img = np.zeros((height, width), np.uint8)
        cv2.circle(circle_img, (x, y), int(r), 1, thickness=-1)
        img = cv2.bitwise_and(img, img, mask=circle_img)
        img = crop_image_from_gray(img)
        if img is None:
            return None
        img = cv2.addWeighted(img, 4, cv2.GaussianBlur(img, (0, 0), sigmaX), -4, 128)
        return img
    except Exception as e:
        print(f"ERROR: Failed during circle crop. Reason: {e}")
        return None

def preprocess_image(file, source, destination):
    try:
        input_filepath = os.path.join('.', source, f'{file}.png')
        output_dir = os.path.join('.', destination)
        output_filepath = os.path.join(output_dir, f'{file}.png')
        os.makedirs(output_dir, exist_ok=True)
        img = cv2.imread(input_filepath)
        if img is None:
            print(f"WARNING: Image {input_filepath} not found or could not be loaded.")
            return
        resized_img = cv2.resize(img, (IMG_SIZE, IMG_SIZE))
        resized_img = circle_crop(resized_img)
        if resized_img is None:
            print(f"WARNING: Preprocessing failed for {input_filepath}.")
            return
        cv2.imwrite(output_filepath, cv2.resize(resized_img, (IMG_SIZE, IMG_SIZE)))
        print(f"SUCCESS: Processed and saved image at {output_filepath}")
    except Exception as e:
        print(f"ERROR: Failed to preprocess image {file}. Reason: {e}")
