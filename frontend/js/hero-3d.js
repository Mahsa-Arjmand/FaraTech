import * as THREE from 'three';

export function initHero3D() {
  const canvas = document.getElementById('heroCanvas');
  if (!canvas) return;

  const heroSection = canvas.closest('.hero') || canvas.parentElement;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000);
  camera.position.set(0, 0, 30);

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  function resizeRenderer() {
    const width = Math.max(heroSection.clientWidth, 1);
    const height = Math.max(heroSection.clientHeight, 1);
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height, false);
  }

  resizeRenderer();

  // === Particle Network ===
  const particleCount = 2200;
  const positions = new Float32Array(particleCount * 3);
  const colors = new Float32Array(particleCount * 3);
  const sizes = new Float32Array(particleCount);
  const color1 = new THREE.Color(0x3b82f6);
  const color2 = new THREE.Color(0xf5b041);

  for (let i = 0; i < particleCount; i++) {
    const i3 = i * 3;
    positions[i3] = (Math.random() - 0.5) * 90;
    positions[i3 + 1] = (Math.random() - 0.5) * 60;
    positions[i3 + 2] = (Math.random() - 0.5) * 60;

    const mixed = color1.clone().lerp(color2, Math.random() * 0.5);
    colors[i3] = mixed.r;
    colors[i3 + 1] = mixed.g;
    colors[i3 + 2] = mixed.b;
    sizes[i] = Math.random() * 0.4 + 0.1;
  }

  const particleGeo = new THREE.BufferGeometry();
  particleGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  particleGeo.setAttribute('color', new THREE.BufferAttribute(colors, 3));

  const particleMat = new THREE.PointsMaterial({
    size: 0.22,
    vertexColors: true,
    transparent: true,
    opacity: 0.75,
    blending: THREE.AdditiveBlending,
    depthWrite: false,
  });

  const particles = new THREE.Points(particleGeo, particleMat);
  scene.add(particles);

  // === Connection Lines ===
  const linePositions = [];
  const lineColors = [];
  const step = 8;

  for (let i = 0; i < particleCount; i += step) {
    const i3 = i * 3;
    const x1 = positions[i3], y1 = positions[i3 + 1], z1 = positions[i3 + 2];

    for (let j = i + step; j < Math.min(i + step * 4, particleCount); j += step) {
      const j3 = j * 3;
      const x2 = positions[j3], y2 = positions[j3 + 1], z2 = positions[j3 + 2];
      const dx = x2 - x1, dy = y2 - y1, dz = z2 - z1;
      const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);

      if (dist < 20) {
        linePositions.push(x1, y1, z1, x2, y2, z2);
        lineColors.push(
          colors[i3], colors[i3 + 1], colors[i3 + 2],
          colors[j3], colors[j3 + 1], colors[j3 + 2]
        );
      }
    }
  }

  const lineGeo = new THREE.BufferGeometry();
  lineGeo.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
  lineGeo.setAttribute('color', new THREE.Float32BufferAttribute(lineColors, 3));

  const lineMat = new THREE.LineBasicMaterial({
    vertexColors: true,
    transparent: true,
    opacity: 0.08,
    blending: THREE.AdditiveBlending,
  });

  const lines = new THREE.LineSegments(lineGeo, lineMat);
  scene.add(lines);

  // === Central 3D Object (Torus Knot) ===
  const torusGeo = new THREE.TorusKnotGeometry(5, 1.4, 100, 16);
  const torusMat = new THREE.MeshPhongMaterial({
    color: 0x3b82f6,
    emissive: 0x1a3a6a,
    wireframe: true,
    transparent: true,
    opacity: 0.22,
  });
  const torusKnot = new THREE.Mesh(torusGeo, torusMat);
  torusKnot.position.set(12, -2, -8);
  scene.add(torusKnot);

  // === Gold Icosahedron ===
  const icosaGeo = new THREE.IcosahedronGeometry(4, 1);
  const icosaMat = new THREE.MeshPhongMaterial({
    color: 0xf5b041,
    emissive: 0x5a3a0a,
    wireframe: true,
    transparent: true,
    opacity: 0.18,
  });
  const icosa = new THREE.Mesh(icosaGeo, icosaMat);
  icosa.position.set(-16, 6, -14);
  scene.add(icosa);

  // === Floating Octahedron ===
  const octaGeo = new THREE.OctahedronGeometry(2.5, 0);
  const octaMat = new THREE.MeshPhongMaterial({
    color: 0x3b82f6,
    emissive: 0x1a3a6a,
    wireframe: true,
    transparent: true,
    opacity: 0.3,
  });
  const octa = new THREE.Mesh(octaGeo, octaMat);
  octa.position.set(-8, -8, -5);
  scene.add(octa);

  // === Lights ===
  scene.add(new THREE.AmbientLight(0x3b82f6, 0.5));

  const light1 = new THREE.PointLight(0x3b82f6, 1.2, 80);
  light1.position.set(15, 15, 15);
  scene.add(light1);

  const light2 = new THREE.PointLight(0xf5b041, 0.8, 80);
  light2.position.set(-20, -10, 15);
  scene.add(light2);

  const light3 = new THREE.PointLight(0x60a5fa, 0.6, 60);
  light3.position.set(0, 20, 10);
  scene.add(light3);

  // === Mouse & Scroll Interaction ===
  let mouseX = 0, mouseY = 0;
  let scrollY = 0;
  let targetScrollY = 0;

  window.addEventListener('mousemove', (e) => {
    mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
    mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
  });

  window.addEventListener('scroll', () => {
    targetScrollY = window.scrollY;
  });

  // === Animation Loop ===
  let time = 0;

  function animate() {
    requestAnimationFrame(animate);
    time += 0.001;

    // Smooth scroll
    scrollY += (targetScrollY - scrollY) * 0.05;

    // Camera drift based on mouse + scroll
    const scrollFactor = Math.min(scrollY / 800, 1);
    camera.position.x += (mouseX * 4 - camera.position.x) * 0.03;
    camera.position.y += (-mouseY * 3 - scrollY * 0.01 - camera.position.y) * 0.03;
    camera.position.z = 30 + scrollFactor * 15;
    camera.lookAt(scene.position);

    // Particles rotation
    particles.rotation.y = time * 0.25 + scrollY * 0.0005;
    particles.rotation.x = Math.sin(time * 0.2) * 0.05;

    lines.rotation.y = time * 0.25 + scrollY * 0.0005;
    lines.rotation.x = Math.sin(time * 0.2) * 0.05;

    // Objects animation
    torusKnot.rotation.x = time * 0.6 + scrollY * 0.001;
    torusKnot.rotation.y = time * 0.9;
    torusKnot.position.y = -2 + Math.sin(time * 0.8) * 1.5;

    icosa.rotation.x = time * 0.4;
    icosa.rotation.z = time * 0.7;
    icosa.position.y = 6 + Math.sin(time * 0.6) * 2;

    octa.rotation.x = time * 1.2;
    octa.rotation.y = time * 0.8;
    octa.position.y = -8 + Math.cos(time * 0.7) * 2;

    renderer.render(scene, camera);
  }

  animate();

  window.addEventListener('resize', resizeRenderer);

  if (typeof ResizeObserver !== 'undefined') {
    const ro = new ResizeObserver(resizeRenderer);
    ro.observe(heroSection);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initHero3D);
} else {
  initHero3D();
}