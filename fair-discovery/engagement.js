(() => {
  const start = Date.now();
  let maxDepth = 0;
  let interactions = 0;
  let pixelFired = false;
  let visitorCounted = false;

  // ----------------------------------------------------
  // 🟢 CONFIGURATION
  // ----------------------------------------------------
  const FACEBOOK_PIXEL_ID = "621310076625940"; 
  const GOOGLE_ADS_ID     = "AW-10797183966"; // <--- Your ID is now set
  // ----------------------------------------------------

  function reportVisitor() {
    if (visitorCounted) return;
    visitorCounted = true;
    // Tell the backend to count 1 Human Visitor
    fetch("/fair-discovery/record.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ type: "visit" })
    }).catch(e => console.log("Tracker error:", e));
  }

  function triggerSmartPixel() {
    if (pixelFired) return; 
    pixelFired = true;

    console.log("[FairDiscovery] Human detected. Firing Tracking Pixels... 🎯");
    
    // 1. Internal Counter (Week 1 Stat)
    reportVisitor();

    // 2. Fire Facebook Pixel
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', FACEBOOK_PIXEL_ID);
    fbq('track', 'PageView'); 

    // 3. Fire Google Ads Tag (Dynamically Loaded)
    if (GOOGLE_ADS_ID) {
        // Load the Google Library only NOW (when human is confirmed)
        const script = document.createElement('script');
        script.src = `https://www.googletagmanager.com/gtag/js?id=${GOOGLE_ADS_ID}`;
        script.async = true;
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', GOOGLE_ADS_ID);
        console.log("Google Ads Tag Fired ✅");
    }
  }

  // 1. Scroll Detection
  window.addEventListener("scroll", () => {
    const depth = window.scrollY / (document.body.scrollHeight - window.innerHeight);
    maxDepth = Math.max(maxDepth, depth);
    if (maxDepth > 0.01) triggerSmartPixel();
  });

  // 2. Interaction Detection
  ["click", "mousemove", "keydown", "touchstart"].forEach(evt => {
    window.addEventListener(evt, () => {
      interactions++;
      // Wait 1 second before verifying human
      if (Date.now() - start > 1000) {
          triggerSmartPixel();
      }
    });
  });

  // 3. Send Engagement SCORE (on exit)
  window.addEventListener("beforeunload", (e) => {
    if (interactions === 0 && maxDepth === 0) return;

    const timeSpent = (Date.now() - start) / 1000;
    const score = Math.round((timeSpent * (maxDepth + 0.1) * (interactions + 1)) / 5);
    
    const data = JSON.stringify({ 
        type: "score", 
        path: location.pathname, 
        score: score 
    });
    
    const url = "/fair-discovery/record.php";
    if (!navigator.sendBeacon(url, data)) {
       fetch(url, { method: "POST", body: data, keepalive: true });
    }
  });
})();