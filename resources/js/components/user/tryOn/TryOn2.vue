ere is the code... that should be base...u are able to change and enhance it if I instruct u <template> <section class="content"> <div class="card card-solid"> <div class="card-body pb-0"> <div class="row"> <div v-for="(item, index) in glasses" :key="index"
                     class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch flex-column"> <div class="card bg-light d-flex flex-fill"> <div class="card-header text-muted border-bottom-0">
{{ item.name }} </div> <div class="card-body pt-0"> <div class="row"> <div class="col-5"> <h2 class="lead"><b>{{ item.price }} BDT</b></h2> <p class="text-muted text-sm"> <b>About: </b> Stylish Glasses </p> </div> <div class="col-7 text-center"> <img :src="item.imgURL" alt="glass-image" class="img-fluid" /> </div> </div> </div> <div class="card-footer"> <div class="text-right">
\<button class="btn btn-danger ml-2" data-toggle="modal" data-target="#modal-try-on"
@click="onTryClick(item)"> <i class="fa-solid fa-play"></i> Try </button> <button class="btn btn-success ml-2"> <i class="fa-solid fa-cart-shopping"></i> Add to cart </button> </div> </div> </div> </div> </div> </div> </div> </section>

```
<!-- Modal -->
<div class="modal fade" id="modal-try-on">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Virtual Try-On: {{ selectedGlass?.name }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" @click="stopCamera">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <video ref="videoRef" autoplay playsinline style="display: none"></video>
                    <div ref="threeContainer" style="width: 100%; max-width: 640px; height: 480px; margin: auto">
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal" @click="stopCamera">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
```

</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'
import * as THREE from 'three'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader'
import {
    FaceLandmarker,
    FilesetResolver
} from '@mediapipe/tasks-vision'

// Glasses list
const glasses = ref([
    {
        name: 'Wood glasses',
        price: '1200',
        imgURL: '/images/defaults/upload.jpg',
        glass_id: '1',
        modelPath: '/glasses/glasses_1.glb'
    },
    {
        name: 'Flex glasses',
        price: '1500',
        imgURL: '/images/defaults/upload.jpg',
        glass_id: '2',
        modelPath: '/glasses/glasses_2.glb'
    }
])

// Refs
const selectedGlass = ref(null)
const videoRef = ref(null)
const threeContainer = ref(null)

let faceLandmarker = null
let scene, camera, renderer, model, rafId
let videoTexture
let modelLoaded = false

// Initialize face tracking
async function initFaceLandmarker() {
    if (faceLandmarker) return
    const vision = await FilesetResolver.forVisionTasks('/wasm')
    faceLandmarker = await FaceLandmarker.createFromOptions(vision, {
        baseOptions: {
            modelAssetPath: '/models/face_landmarker.task'
        },
        runningMode: 'VIDEO',
        numFaces: 1
    })
}

// Create scene
async function initThreeScene() {
    scene = new THREE.Scene()
    camera = new THREE.PerspectiveCamera(50, 640/480, 0.1, 2000)
    camera.position.z = 2

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
    renderer.setSize(640, 480)
    threeContainer.value.innerHTML = ''
    threeContainer.value.appendChild(renderer.domElement)

    const light = new THREE.DirectionalLight(0xffffff, 1)
    light.position.set(0, 0, 2)
    scene.add(light)

    videoTexture = new THREE.VideoTexture(videoRef.value)
    scene.background = videoTexture
}

// Load selected glass model
async function loadGlassModel(path) {
    if (model) {
        scene.remove(model)
        model = null
    }

    const loader = new GLTFLoader()
    loader.load(
        path,
        (gltf) => {
            model = gltf.scene
            model.scale.set(0.12, 0.12, 0.12)
            scene.add(model)
            modelLoaded = true
        },
        undefined,
        (error) => {
            console.error('Error loading model:', error)
        }
    )
}

// Animation loop
function animate() {
    rafId = requestAnimationFrame(animate)
    renderer.render(scene, camera)

    if (!modelLoaded || !faceLandmarker) return
    
    const nowInMs = Date.now()
    const results = faceLandmarker.detectForVideo(videoRef.value, nowInMs)

    if (results.faceLandmarks.length > 0) {
        const face = results.faceLandmarks[0]

        const leftEye = face[33]
        const rightEye = face[263]
        const noseBridge = face[168] // More central than 1

        // --- Center Position (between eyes & nose bridge) ---
        const x = (leftEye.x + rightEye.x + noseBridge.x) / 3
        const y = (leftEye.y + rightEye.y + noseBridge.y) / 3
        const z = (leftEye.z + rightEye.z + noseBridge.z) / 3

        const posX = (x - 0.5) * 2
        const posY = -(y - 0.5) * 2
        const posZ = (z - 0.5) * 2

        model.position.set(posX, posY, posZ)

        // --- Orientation ---
        const dx = rightEye.x - leftEye.x
        const dy = rightEye.y - leftEye.y
        const roll = Math.atan2(dy, dx)

        // Properly apply head tilt using quaternion
        const euler = new THREE.Euler(0, 0, roll)
        model.setRotationFromEuler(euler)


        // --- Scaling ---
        const eyeDist = Math.sqrt(
            (rightEye.x - leftEye.x) ** 2 +
            (rightEye.y - leftEye.y) ** 2 +
            (rightEye.z - leftEye.z) ** 2
        )

        const scale = eyeDist * 4.0 // adjust as needed
        model.scale.set(scale, scale, scale)
    }
}



// Start webcam
async function startCamera() {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true })
    videoRef.value.srcObject = stream
    await videoRef.value.play()

    await initThreeScene()
    await loadGlassModel(selectedGlass.value.modelPath)
    animate()
}

// Stop webcam & animation
function stopCamera() {
    if (rafId) cancelAnimationFrame(rafId)
    const stream = videoRef.value?.srcObject
    if (stream) stream.getTracks().forEach((track) => track.stop())
    modelLoaded = false

    if (scene && model) {
        scene.remove(model)
        model = null
    }
}

// When user clicks "Try"
async function onTryClick(glass) {
    selectedGlass.value = glass
    setTimeout(async () => {
        await initFaceLandmarker()
        await startCamera()
    }, 500)
}

// Cleanup
onBeforeUnmount(() => {
    stopCamera()
})
</script>
