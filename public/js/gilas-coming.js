(() => {
  const ready = (fn) => document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", fn, { once: true })
    : fn();

  ready(() => {
    if (!window.gsap) return;

    const cleanups = [];
    const ctx = gsap.context(() => {
      initAmbient();
      initLeader();
      initReveal();
      initForm();
    });
    window.gilasGsapContext = ctx;

    window.addEventListener("pagehide", () => {
      cleanups.forEach((fn) => fn());
      ctx.revert();
    }, { once: true });

    function initAmbient() {
      const spot = document.querySelector("[data-spot]");
      const pivot = document.querySelector("[data-beam]");
      if (spot) {
        const moveSpot = (time) => {
          const w = innerWidth, h = innerHeight;
          const t = time / 1000;
          const x = w * (.52 + .28 * Math.sin(t * .083) + .12 * Math.sin(t * .191));
          const y = h * (.46 + .22 * Math.sin(t * .117) + .10 * Math.sin(t * .237));
          gsap.set(spot, { x, y });
        };
        gsap.ticker.add(moveSpot);
        cleanups.push(() => gsap.ticker.remove(moveSpot));
      }
      if (pivot && matchMedia("(pointer:fine) and (prefers-reduced-motion:no-preference)").matches) {
        const onMove = (e) => {
          const nx = (e.clientX / innerWidth - .5) * 2;
          const ny = (e.clientY / innerHeight - .5) * 2;
          gsap.to(pivot, { rotate: nx * 1.6, y: ny * 4, duration: .8, ease: "power3.out", overwrite: true });
        };
        addEventListener("pointermove", onMove, { passive: true });
        cleanups.push(() => removeEventListener("pointermove", onMove));
      }
      const dust = document.querySelector("[data-dust]");
      const cx = dust?.getContext("2d");
      if (cx) {
        let w=0,h=0;
        const specks = Array.from({length:34},()=>({x:Math.random(),y:Math.random(),r:.5+Math.random()*1.5,v:.02+Math.random()*.05,a:.1+Math.random()*.28,p:Math.random()*Math.PI*2}));
        const resize=()=>{w=dust.clientWidth;h=dust.clientHeight;const d=Math.min(devicePixelRatio||1,2);dust.width=w*d;dust.height=h*d;cx.setTransform(d,0,0,d,0,0)};
        resize(); addEventListener("resize",resize);
        const draw=(time,delta)=>{
          if(!w||!h)return;
          const dt=Math.min(delta,100)/1000;
          cx.clearRect(0,0,w,h);cx.fillStyle="#F0B44E";
          specks.forEach(s=>{s.y-=s.v*dt;if(s.y<-.02){s.y=1.02;s.x=Math.random()}const x=s.x*w+Math.sin(time*.0026+s.p)*10;const y=s.y*h;cx.globalAlpha=s.a;cx.beginPath();cx.arc(x,y,s.r,0,Math.PI*2);cx.fill()});cx.globalAlpha=1;
        };
        gsap.ticker.add(draw); cleanups.push(()=>{gsap.ticker.remove(draw);removeEventListener("resize",resize)});
      }
      const grain = document.querySelector("[data-grain]");
      const gcx = grain?.getContext("2d");
      if (gcx) {
        const tile=document.createElement("canvas");tile.width=96;tile.height=96;const tc=tile.getContext("2d");const img=tc.createImageData(96,96);
        for(let i=0;i<img.data.length;i+=4){const v=96+Math.random()*120;img.data[i]=img.data[i+1]=img.data[i+2]=v;img.data[i+3]=255}tc.putImageData(img,0,0);
        const pattern=gcx.createPattern(tile,"repeat"); let acc=0,w=0,h=0;
        const resize=()=>{w=innerWidth;h=innerHeight;grain.width=w;grain.height=h;gcx.fillStyle=pattern}; resize();addEventListener("resize",resize);
        const draw=(time,delta)=>{acc+=delta/1000;if(acc<1/24)return;acc%=1/24;const ox=Math.floor(Math.random()*96),oy=Math.floor(Math.random()*96);gcx.setTransform(1,0,0,1,-ox,-oy);gcx.fillRect(ox,oy,w,h)};
        gsap.ticker.add(draw);cleanups.push(()=>{gsap.ticker.remove(draw);removeEventListener("resize",resize)});
      }
    }

    function initLeader() {
      const block=document.querySelector("[data-leader]"); if(!block)return;
      const target=new Date(block.dataset.open).getTime(); if(Number.isNaN(target))return;
      const sweep=block.querySelector("[data-leader-sweep]"), num=block.querySelector("[data-leader-num]"), key=block.querySelector("[data-leader-key]"), sub=block.querySelector("[data-leader-sub]");
      const reduce=matchMedia("(prefers-reduced-motion:reduce)").matches; let lastDay=-1,lastMin=-1;
      const write=()=>{const now=Date.now();if(!reduce&&sweep)gsap.set(sweep,{rotation:(now%60000)/60000*360});const ms=target-now;if(ms<=0){num.textContent="۰";key.textContent="اکنون باز است";sub.textContent="";return}const days=Math.floor(ms/86400000),mins=Math.floor(ms/60000)%60,hours=Math.floor(ms/3600000)%24;if(days!==lastDay){lastDay=days;num.textContent=String(days).replace(/\d/g,d=>"۰۱۲۳۴۵۶۷۸۹"[d]);key.textContent=days===1?"یک روز تا افتتاح":"روز تا افتتاح"}if(mins!==lastMin){lastMin=mins;sub.textContent=hours+" ساعت و "+mins+" دقیقه"}};
      write();
      if(reduce){const id=setInterval(write,30000);cleanups.push(()=>clearInterval(id))}else{gsap.ticker.add(write);cleanups.push(()=>gsap.ticker.remove(write))}
    }

    function initReveal() {
      const slide=document.querySelector("[data-slide]"), leader=document.querySelector("[data-leader]"), lines=gsap.utils.toArray(".line-inner"), rule=document.querySelector(".rule-bar");
      const tl=gsap.timeline({defaults:{ease:"power3.out"}});
      if(slide){gsap.set(slide,{opacity:0,y:18,scale:.988});tl.to(slide,{opacity:1,y:0,scale:1,duration:1.1},.1)}
      if(lines.length){gsap.set(lines,{yPercent:112});tl.to(lines,{yPercent:0,duration:1.05,stagger:.1,ease:"power4.out"},.35)}
      if(rule){gsap.set(rule,{scaleX:0,transformOrigin:"right center"});tl.to(rule,{scaleX:1,duration:1.2,ease:"power2.inOut"},.8)}
      if(leader){gsap.set(leader,{opacity:0,scale:.9});tl.to(leader,{opacity:1,scale:1,duration:.9,ease:"back.out(1.4)"},1)}
    }

    function initForm() {
      const form=document.querySelector("[data-notify]");if(!form)return;
      const field=form.querySelector(".notify-field"),done=form.querySelector(".notify-done"),status=form.querySelector(".notify-status");
      form.addEventListener("submit",(e)=>{e.preventDefault();if(!form.checkValidity()){form.reportValidity();return}status.textContent="ثبت شد؛ هنگام افتتاح فقط یک پیام برایتان می‌فرستیم.";gsap.timeline().to(field,{opacity:0,y:-8,duration:.25}).set(field,{display:"none"}).call(()=>done.hidden=false).set(done,{opacity:0,y:10}).to(done,{opacity:1,y:0,duration:.45,ease:"back.out(1.5)"})});
    }
  });
})();