document.addEventListener('DOMContentLoaded', function(){
  // Add placeholder images to cards that don't have data-image attribute
  const cardImgs = Array.from(document.querySelectorAll('.card .img'));
  cardImgs.forEach(imgEl => {
    if(!imgEl.dataset.image) {
      // Use a placeholder service or the background image
      // For now, we'll use a food placeholder API
      const card = imgEl.closest('.card');
      const foodName = card ? (card.querySelector('h3')?.textContent || 'food') : 'food';
      // You can replace this with actual image URLs later
      imgEl.dataset.image = `https://source.unsplash.com/400x300/?food,${encodeURIComponent(foodName)}`;
      // Also set as background for visual display
      imgEl.style.backgroundImage = `url(${imgEl.dataset.image})`;
      imgEl.style.backgroundSize = 'cover';
      imgEl.style.backgroundPosition = 'center';
    }
  });
  
  // target elements: elements with background images (.card .img) or plain <img>
  const targets = Array.from(document.querySelectorAll('.card .img, img'));

  function extractImageUrl(el){
    // First check for data-image attribute (highest priority)
    if(el.dataset && el.dataset.image) return el.dataset.image;
    
    // If element has a background-image CSS property, use it
    const cs = window.getComputedStyle(el);
    const bg = cs.backgroundImage;
    if(bg && bg !== 'none'){
      // backgroundImage looks like: url("path") or url(path)
      const m = bg.match(/url\(["']?(.*?)["']?\)/);
      if(m && m[1]) return m[1];
    }
    // if it's an <img>
    if(el.tagName && el.tagName.toLowerCase() === 'img' && el.src) return el.src;
    // try to find an <img> inside
    const img = el.querySelector && el.querySelector('img');
    if(img && img.src) return img.src;
    return null;
  }

  function openLightbox(src, alt){
    if(!src) return;
    // create overlay
    let overlay = document.createElement('div');
    overlay.className = 'lb-overlay';

    const wrapper = document.createElement('div');
    wrapper.className = 'lb-image-wrapper';
    const image = document.createElement('img');
    image.src = src;
    if(alt) image.alt = alt;

    wrapper.appendChild(image);
    overlay.appendChild(wrapper);

    // close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'lb-close';
    closeBtn.innerText = 'Kapat';
    closeBtn.addEventListener('click', close);

    // caption (optional)
    const caption = document.createElement('div');
    caption.className = 'lb-caption';
    if(alt) caption.textContent = alt;

    document.body.appendChild(overlay);
    document.body.appendChild(closeBtn);
    if(alt) document.body.appendChild(caption);

    // force reflow then show
    requestAnimationFrame(()=> overlay.classList.add('visible'));

    function close(){
      overlay.classList.remove('visible');
      overlay.addEventListener('transitionend', ()=> {
        if(overlay.parentNode) overlay.parentNode.removeChild(overlay);
      });
      if(closeBtn.parentNode) closeBtn.parentNode.removeChild(closeBtn);
      if(caption.parentNode) caption.parentNode.removeChild(caption);
      document.removeEventListener('keydown', onKey);
    }

    overlay.addEventListener('click', function(e){ if(e.target === overlay) close(); });
    function onKey(e){ if(e.key === 'Escape') close(); }
    document.addEventListener('keydown', onKey);
  }

  targets.forEach(el => {
    // only add if there's an image to show
    const src = extractImageUrl(el);
    if(!src) return;
    el.style.cursor = 'zoom-in';
    el.addEventListener('click', (e)=>{
      e.preventDefault();
      e.stopPropagation();
      // Get food name from the card for caption
      const card = el.closest('.card');
      const foodName = card ? (card.querySelector('h3')?.textContent || '') : '';
      const alt = (el.getAttribute && el.getAttribute('alt')) || (el.dataset && el.dataset.caption) || foodName;
      openLightbox(src, alt);
    });
  });
  
  // Also make entire cards clickable if they contain .img elements
  const cards = Array.from(document.querySelectorAll('.card'));
  cards.forEach(card => {
    const imgEl = card.querySelector('.img');
    if(imgEl) {
      const src = extractImageUrl(imgEl);
      if(src) {
        card.style.cursor = 'pointer';
        card.addEventListener('click', (e)=>{
          // Don't trigger if clicking on content area (to allow other interactions)
          if(e.target.closest('.content')) return;
          e.preventDefault();
          e.stopPropagation();
          const foodName = card.querySelector('h3')?.textContent || '';
          openLightbox(src, foodName);
        });
      }
    }
  });
});
