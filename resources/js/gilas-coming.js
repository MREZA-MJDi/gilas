import { gsap } from 'gsap';
import { DrawSVGPlugin } from 'gsap/DrawSVGPlugin';
import { MotionPathPlugin } from 'gsap/MotionPathPlugin';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { CustomEase } from 'gsap/CustomEase';

gsap.registerPlugin(DrawSVGPlugin, MotionPathPlugin, ScrollTrigger, CustomEase);

(() => {
  const start = () => {

    const tree = document.querySelector('[data-tree]');
    if (!tree) return;

    const hasDraw = true;
    const hasMotion = true;
    const hasScroll = true;
    const hasEase = true;

    try { CustomEase.create('clip', '.57,0,.43,1'); } catch (_) {}

    const cleanups = [];

    initAmbient();
    initCounter();
    initTree();
    initHover();

    function initAmbient() {
      const spot = document.querySelector('[data-spot]');
      const beam = document.querySelector('[data-beam]');
      if (spot && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const move = (time) => {
          const t = time / 1000;
          const x = innerWidth * (0.5 + 0.23 * Math.sin(t * 0.083) + 0.1 * Math.sin(t * 0.19));
          const y = innerHeight * (0.5 + 0.19 * Math.sin(t * 0.117) + 0.08 * Math.sin(t * 0.237));
          gsap.set(spot, { x, y });
        };
        gsap.ticker.add(move);
        cleanups.push(() => gsap.ticker.remove(move));
      }
      if (beam && matchMedia('(pointer:fine) and (prefers-reduced-motion: no-preference)').matches) {
        const onMove = (e) => {
          const nx = (e.clientX / innerWidth - 0.5) * 2;
          const ny = (e.clientY / innerHeight - 0.5) * 2;
          gsap.to(beam, { rotation: nx * 1.35, y: ny * 5, duration: .8, ease: 'power3.out', overwrite: true });
        };
        addEventListener('pointermove', onMove, { passive: true });
        cleanups.push(() => removeEventListener('pointermove', onMove));
      }

      const dust = document.querySelector('[data-dust]');
      const cx = dust?.getContext('2d');
      if (cx) {
        let w = 0, h = 0;
        const specks = Array.from({ length: 28 }, () => ({
          x: Math.random(), y: Math.random(), r: .4 + Math.random() * 1.3,
          v: .018 + Math.random() * .045, a: .08 + Math.random() * .2, p: Math.random() * Math.PI * 2
        }));
        const resize = () => {
          w = dust.clientWidth; h = dust.clientHeight;
          const d = Math.min(devicePixelRatio || 1, 2);
          dust.width = w * d; dust.height = h * d;
          cx.setTransform(d, 0, 0, d, 0, 0);
        };
        resize();
        addEventListener('resize', resize);
        const draw = (time, delta) => {
          if (!w || !h) return;
          const dt = Math.min(delta, 100) / 1000;
          cx.clearRect(0, 0, w, h);
          cx.fillStyle = '#ffffff';
          for (const s of specks) {
            s.y -= s.v * dt;
            if (s.y < -.02) { s.y = 1.02; s.x = Math.random(); }
            const x = s.x * w + Math.sin(time * .0024 + s.p) * 9;
            const y = s.y * h;
            cx.globalAlpha = s.a;
            cx.beginPath(); cx.arc(x, y, s.r, 0, Math.PI * 2); cx.fill();
          }
          cx.globalAlpha = 1;
        };
        gsap.ticker.add(draw);
        cleanups.push(() => {
          gsap.ticker.remove(draw);
          removeEventListener('resize', resize);
        });
      }

      const grain = document.querySelector('[data-grain]');
      const gcx = grain?.getContext('2d');
      if (gcx && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const tile = document.createElement('canvas');
        tile.width = 96; tile.height = 96;
        const tc = tile.getContext('2d');
        const img = tc.createImageData(96, 96);
        for (let i = 0; i < img.data.length; i += 4) {
          const v = 90 + Math.random() * 120;
          img.data[i] = img.data[i + 1] = img.data[i + 2] = v; img.data[i + 3] = 255;
        }
        tc.putImageData(img, 0, 0);
        const pattern = gcx.createPattern(tile, 'repeat');
        let acc = 0;
        const draw = (_, delta) => {
          acc += delta / 1000;
          if (acc < 1/20) return;
          acc %= 1/20;
          const ox = Math.floor(Math.random() * 96);
          const oy = Math.floor(Math.random() * 96);
          gcx.setTransform(1, 0, 0, 1, -ox, -oy);
          gcx.fillStyle = pattern;
          gcx.fillRect(ox, oy, innerWidth, innerHeight);
        };
        gsap.ticker.add(draw);
        cleanups.push(() => gsap.ticker.remove(draw));
      }
    }

    function initCounter() {
      const block = document.querySelector('[data-leader]');
      if (!block) return;
      const target = new Date(block.dataset.open).getTime();
      if (Number.isNaN(target)) return;

      const num = block.querySelector('[data-leader-num]');
      const key = block.querySelector('[data-leader-key]');
      const sub = block.querySelector('[data-leader-sub]');
      const sweep = block.querySelector('[data-leader-sweep]');
      let last = '';

      const fa = (value) => String(value).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
      const write = () => {
        const diff = target - Date.now();
        if (diff <= 0) {
          num.textContent = '۰';
          key.textContent = 'اکنون باز است';
          sub.textContent = '';
          return;
        }
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor(diff / 3600000) % 24;
        const minutes = Math.floor(diff / 60000) % 60;
        const value = days + ':' + hours + ':' + minutes;
        if (value !== last) {
          last = value;
          num.textContent = fa(days);
          key.textContent = days === 1 ? 'یک روز تا افتتاح' : 'روز تا افتتاح';
          sub.textContent = fa(hours) + ' ساعت و ' + fa(minutes) + ' دقیقه';
        }
        if (sweep && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
          const sec = (Date.now() % 60000) / 60000 * 360;
          gsap.set(sweep, { rotation: sec });
        }
      };
      write();
      const id = setInterval(write, 1000);
      cleanups.push(() => clearInterval(id));
    }

    function initTree() {
      const svg = tree.querySelector('.tree-svg');
      const tl = gsap.timeline({
        defaults: { duration: .8, ease: 'power2.out' }
      });

      gsap.set(tree, { opacity: 1, scale: .84, yPercent: 7 });
      gsap.set(svg, { display: 'block' });

      const paths = [
        ['.tree-svg__bottom', '100%'],
        ['.tree-svg__top', '100%'],
        ['.tree-svg__left', '75%'],
        ['.tree-svg__right', '75%'],
        ['.tree-svg__right-top', '85%'],
        ['.tree-svg__left-top', '85%']
      ];

      if (hasDraw) {
        for (const [selector, end] of paths) {
          gsap.set(selector, { drawSVG: '0%', strokeWidth: 33 });
        }
        tl.to(tree, { scale: 1, yPercent: 0, duration: 1.15, ease: 'power3.out' }, 0)
          .to('.tree-svg__bottom', { drawSVG: '100%', duration: .62 }, .05)
          .to('.tree-svg__top', { drawSVG: '100%', duration: .62 }, .22)
          .to('.tree-svg__left', { drawSVG: '75%', rotation: 25, transformOrigin: 'bottom right', duration: .62 }, .22)
          .to('.tree-svg__right', { drawSVG: '75%', rotation: -25, transformOrigin: 'bottom left', duration: .62 }, .22)
          .to('.tree-svg__right-top', { drawSVG: '85%', rotation: -13, transformOrigin: 'bottom left', duration: .62 }, .22)
          .to('.tree-svg__left-top', { drawSVG: '85%', rotation: 13, transformOrigin: 'bottom right', duration: .62 }, .22)
          .to('.tree-svg__branches', { drawSVG: '100%', strokeWidth: 3, rotation: 0, duration: .55 }, .74)
          .to('.tree-svg__bottom', { strokeWidth: 3, duration: .55 }, .74);
      } else {
        tl.to(tree, { scale: 1, yPercent: 0, duration: 1.1, ease: 'power3.out' }, 0);
      }

      tl.to('.tree-circle__big', { scale: .65, duration: .45 }, .9)
        .to('.tree-circle__small', { scale: .72, duration: .45 }, .9)
        .to('.tree-circle__big', { scale: 1, duration: .45, ease: 'power2.out' }, 1.35)
        .to('.tree-circle__small', { scale: 1, duration: .45, ease: 'power2.out' }, 1.35)
        .to('.tree-ball', { opacity: 1, scale: 1, duration: .4, stagger: .06 }, 1.55);

      if (hasScroll) {
        const scrollTl = gsap.timeline({
          scrollTrigger: {
            trigger: '#main',
            start: 'top top',
            end: '+=4200',
            scrub: 1.1,
            invalidateOnRefresh: true
          }
        });
        scrollTl
          .to(tree, { scale: 1.07, yPercent: -2, duration: 1.7, ease: 'none' })
          .to(tree.querySelector('.tree-wrapp'), { y: '-=0.16rem', duration: 1.2, ease: 'none' }, '<');
      }

      tl.eventCallback('onComplete', () => {
        initBallMotion();
      });
    }

    function initBallMotion() {
      if (!hasMotion) return;
      document.querySelectorAll('.tree-ball').forEach((ball) => {
        gsap.to(ball, {
          y: '+=6',
          duration: 1.9 + Math.random() * .7,
          repeat: -1,
          yoyo: true,
          ease: 'sine.inOut',
          delay: Math.random() * .5
        });
      });
    }

    function initHover() {
      const circle = gsap.timeline({ paused: true })
        .to('.tree-circle__big', { boxShadow: '0 0 0 3px var(--tree-line)', duration: .4, ease: 'power1.out' })
        .to('.tree-circle__small', { boxShadow: '0 0 0 1px var(--tree-line)', duration: .4, ease: 'power1.out' }, '<');

      document.querySelectorAll('.tree-ball').forEach((ball) => {
        const pos = ball.dataset.position;
        const branch = document.querySelector('.tree-svg__branches[data-position="' + pos + '"]');
        if (!branch) return;

        const hover = gsap.timeline({ paused: true });
        hover
          .to(ball.querySelector('.tree-ball__1'), { scale: 2.25, duration: .4, ease: 'power1.inOut' })
          .to(branch, { strokeWidth: 8, duration: .4, ease: 'power1.inOut' }, '<')
          .set(ball.querySelector('.tree-ball__2'), { opacity: 1 })
          .set(ball.querySelector('.tree-ball__3'), { opacity: 1 });

        ball.addEventListener('pointerenter', () => { hover.play(); circle.play(); });
        ball.addEventListener('pointerleave', () => { hover.reverse(); circle.reverse(); });
      });
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
})();