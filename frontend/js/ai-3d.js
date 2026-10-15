import * as THREE from 'three';

export function initAI3D() {
  const canvas = document.getElementById('aiCanvas');
  if (!canvas) return;

  const parent = canvas.parentElement;
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, parent.clientWidth / parent.clientHeight, 0.1, 100);
  camera.position.z = 8;

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setSize(parent.clientWidth, parent.clientHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  // Neural network nodes
  const nodeCount = 60;
  const nodes = [];
  const nodeGeo = new THREE.SphereGeometry(0.08, 8, 8);

  for (let i = 0; i < nodeCount; i++) {
    const mat = new THREE.MeshBasicMaterial({
      color: Math.random() > 0.7 ? 0xf5b041 : 0x3b82f6,
      transparent: true,
      opacity: 0.8,
    });
    const node = new THREE.Mesh(nodeGeo, mat);
    node.position.set(
      (Math.random() - 0.5) * 20,
      (Math.random() - 0.5) * 10,
      (Math.random() - 0.5) * 10
    );
    node.userData = {
      baseY: node.position.y,
      speed: Math.random() * 0.5 + 0.3,
      offset: Math.random() * Math.PI * 2,
    };
    nodes.push(node);
    scene.add(node);
  }

  // Connection lines
  const lineMat = new THREE.LineBasicMaterial({
    color: 0x3b82f6,
    transparent: true,
    opacity: 0.15,
  });

  const lines = [];
  for (let i = 0; i < nodes.length; i++) {
    for (let j = i + 1; j < nodes.length; j++) {
      const dist = nodes[i].position.distanceTo(nodes[j].position);
      if (dist < 4) {
        const geo = new THREE.BufferGeometry().setFromPoints([
          nodes[i].position.clone(),
          nodes[j].position.clone(),
        ]);
        const line = new THREE.Line(geo, lineMat);
        lines.push({ line, a: nodes[i], b: nodes[j] });
        scene.add(line);
      }
    }
  }

  let time = 0;
  function animate() {
    requestAnimationFrame(animate);
    time += 0.01;

    nodes.forEach(node => {
      node.position.y = node.userData.baseY + Math.sin(time * node.userData.speed + node.userData.offset) * 0.5;
    });

    lines.forEach(({ line, a, b }) => {
      const positions = line.geometry.attributes.position.array;
      positions[0] = a.position.x;
      positions[1] = a.position.y;
      positions[2] = a.position.z;
      positions[3] = b.position.x;
      positions[4] = b.position.y;
      positions[5] = b.position.z;
      line.geometry.attributes.position.needsUpdate = true;
    });

    scene.rotation.y = time * 0.05;
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
  document.addEventListener('DOMContentLoaded', initAI3D);
} else {
  initAI3D();
}