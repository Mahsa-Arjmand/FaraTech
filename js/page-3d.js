import * as THREE from 'three';

export function initPage3D() {
  // Try page canvas first, then ai hero canvas
  const canvas = document.getElementById('pageCanvas') || document.getElementById('aiHeroCanvas');
  if (!canvas) return;

  const parent = canvas.parentElement;
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(70, parent.clientWidth / parent.clientHeight, 0.1, 1000);
  camera.position.z = 20;

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setSize(parent.clientWidth, parent.clientHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  // === Floating geometric shapes ===
  const shapes = [];
  const geometries = [
    new THREE.IcosahedronGeometry(1.2, 0),
    new THREE.OctahedronGeometry(1, 0),
    new THREE.TetrahedronGeometry(1.3, 0),
    new THREE.TorusGeometry(0.9, 0.3, 8, 20),
    new THREE.BoxGeometry(1.4, 1.4, 1.4),
  ];

  const colors = [0x3b82f6, 0xf5b041, 0x60a5fa];

  for (let i = 0; i < 18; i++) {
    const geo = geometries[Math.floor(Math.random() * geometries.length)];
    const color = colors[Math.floor(Math.random() * colors.length)];
    const mat = new THREE.MeshPhongMaterial({
      color,
      emissive: color,
      emissiveIntensity: 0.15,
      wireframe: Math.random() > 0.5,
      transparent: true,
      opacity: 0.35 + Math.random() * 0.3,
    });

    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.set(
      (Math.random() - 0.5) * 50,
      (Math.random() - 0.5) * 30,
      (Math.random() - 0.5) * 30 - 5
    );
    mesh.userData = {
      baseY: mesh.position.y,
      rotSpeed: (Math.random() - 0.5) * 0.02,
      floatSpeed: 0.3 + Math.random() * 0.5,
      offset: Math.random() * Math.PI * 2,
      scale: 0.5 + Math.random() * 1.2,
    };
    mesh.scale.setScalar(mesh.userData.scale);
    scene.add(mesh);
    shapes.push(mesh);
  }

  // === Particle grid ===
  const particleCount = 800;
  const positions = new Float32Array(particleCount * 3);
  const colorsArr = new Float32Array(particleCount * 3);

  for (let i = 0; i < particleCount; i++) {
    const i3 = i * 3;
    positions[i3] = (Math.random() - 0.5) * 70;
    positions[i3 + 1] = (Math.random() - 0.5) * 40;
    positions[i3 + 2] = (Math.random() - 0.5) * 40;

    const c = Math.random() > 0.7 ? new THREE.Color(0xf5b041) : new THREE.Color(0x3b82f6);
    colorsArr[i3] = c.r;
    colorsArr[i3 + 1] = c.g;
    colorsArr[i3 + 2] = c.b;
  }

  const particleGeo = new THREE.BufferGeometry();
  particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  particleGeo.setAttribute('color', new THREE.BufferAttribute(colorsArr, 3));

  const particleMat = new THREE.PointsMaterial({
    size: 0.15,
    vertexColors: true,
    transparent: true,
    opacity: 0.5,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
  });

  const particles = new THREE.Points(particleGeo, particleMat);
  scene.add(particles);

  // === Lights ===
  scene.add(new THREE.AmbientLight(0x3b82f6, 0.5));

  const light1 = new THREE.PointLight(0x3b82f6, 1, 80);
  light1.position.set(15, 15, 20);
  scene.add(light1);

  const light2 = new THREE.PointLight(0xf5b041, 0.8, 80);
  light2.position.set(-15, -10, 20);
  scene.add(light2);

  const light3 = new THREE.PointLight(0x60a5fa, 0.5, 60);
  light3.position.set(0, 20, 15);
  scene.add(light3);

  // === Mouse interaction ===
  let mouseX = 0, mouseY = 0;
  window.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
    mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
  });

  // === Scroll ===
  let scrollY = 0;
  window.addEventListener('scroll', () => {
    scrollY = window.scrollY;
  });

  // === Animate ===
  let time = 0;

  function animate() {
    requestAnimationFrame(animate);
    time += 0.01;

    // Camera drift
    camera.position.x += (mouseX * 4 - camera.position.x) * 0.03;
    camera.position.y += (-mouseY * 3 - camera.position.y) * 0.03;
    camera.position.z = 20 + Math.min(scrollY / 100, 10);
    camera.lookAt(scene.position);

    // Shapes animation
    shapes.forEach((shape, i) => {
      shape.rotation.x += shape.userData.rotSpeed;
      shape.rotation.y += shape.userData.rotSpeed * 1.5;
      shape.position.y = shape.userData.baseY + Math.sin(time * shape.userData.floatSpeed + shape.userData.offset) * 1.5;
    });

    particles.rotation.y = time * 0.05;

    renderer.render(scene, camera);
  }

  animate();

  window.addEventListener('resize', () => {
    camera.aspect = parent.clientWidth / parent.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(parent.clientWidth, parent.clientHeight);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPage3D);
} else {
  initPage3D();
}