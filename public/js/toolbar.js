const
  cspCurrentUrl = window.location.href,
  cspIsRoot = location.pathname === '/' || location.pathname === '/preview.php/',
  cspSearchEngines = {
    'microsoftBing': 'www.bing.com',
    'naver': 'searchadvisor.naver.com',
    'seznam.cz': 'search.seznam.cz',
    'yandex': 'yandex.com',
  },
  totalNeutralPercent = 70,
  totalFailPercent = 50,
  cspLang = document.documentElement.lang,
  cspMessages = {
    de: {
      'toolbarHeadline': 'SEO Toolbar',
      'buttonHide': 'Toolbar ausblenden',
      'buttonIndexNow': 'IndexNow',
      'buttonOverlap': 'Layout wechseln',
      'seoAuditHeadline': 'SEO-Audit',
      'mainKeywordNotExistsCheck': 'Gib das Hauptkeyword für diese Seite an.',
      'mainKeywordTitleCheckSuccess': 'Perfekt, das Hauptkeyword ist im Titel enthalten.',
      'mainKeywordTitleCheckFail' : 'Füge das Hauptkeyword zum Meta Titel hinzu.',
      'mainKeywordMetaDescriptionCheckSuccess': 'Perfekt, das Hauptkeyword ist in der Meta Beschreibung enthalten.',
      'mainKeywordMetaDescriptionCheckFail': 'Füge das Hauptkeyword zur Meta Beschreibung hinzu.',
      'mainKeywordAliasCheckSuccess': 'Sehr gut, das Hauptkeyword ist im Alias der Seite enthalten.',
      'mainKeywordAliasCheckNeutral': 'Für die Startseite ist kein Alias notwendig.',
      'mainKeywordAliasCheckFail': 'Nutze das Hauptkeyword im Alias der Seite.',
      'seoAuditReadabilityHeadline': 'Lesbarkeit des Inhalts',
      'imageAltCheckSuccess': 'Super, Bilder sind vorhanden und enthalten das Hauptkeyword im alt-Attribut.',
      'imageAltCheckNeutral': 'Es sind Bilder vorhanden, aber das Hauptkeyword wird nicht als alt-Attribut verwendet.',
      'imageAltCheckFail': 'Auf dieser Seite erscheinen keine Bilder. Füge welche hinzu!',
      'internalLinkCheckSuccess': 'Super, es wurden ausreichend interne Links gefunden.',
      'internalLinkCheckFail': 'Auf dieser Seite gibt es keine internen Links. Füge welche hinzu!',
    }
  }
  ;

  var
    totalTests = 0,
    totalFails = 0
  ;
document.addEventListener('DOMContentLoaded', () => {
  init();
});

function init () {
  let
    tHeader,
    tBody,
    text,
    button,
    container,
    panel,
    body = document.querySelector('body')
  ;

  // Initialize toolbar
  if ('hidden' === localStorage.getItem('pdir/seoToolbar/displayState')) {
    body.classList.add('pdir-seoToolbar--hidden');
  } else {
    body.classList.add('pdir-seoToolbar--visible');
  }

  if ('normal' === localStorage.getItem('pdir/seoToolbar/layoutState')) {
    body.classList.add('pdir-seoToolbar--normal');
  } else {
    body.classList.add('pdir-seoToolbar--overlapped');
  }

  // Add toolbar to html
  // <div class="csp-toolbar"><button class="csp-button" role="button">🔍</button></div>
  let toolbar= document.createElement('div');
  toolbar.className = 'csp-toolbar';
  toolbar.setAttribute('id', 'cspToolbar');

  // check for preview mode
  if (cspCurrentUrl.includes('preview.php')) {
    toolbar.className += ' preview';
  }

  // add toolbar header
  tHeader = document.createElement('div');
  tHeader.className = 'csp-toolbar-header';

  text = document.createElement('span');
  text.textContent = cspMessages[cspLang]['toolbarHeadline'];
  tHeader.append(text);

  text = document.createElement('div');
  text.setAttribute('id', 'cspTotal');
  text.textContent = '0/0';
  tHeader.append(text);

  toolbar.append(tHeader);

  // add toolbar body
  tBody = document.createElement('div');
  tBody.className = 'csp-toolbar-body';
  tBody.setAttribute('id', 'cspToolbarBody');

  // add main keyword
  if (typeof cspMainKeyword !== 'undefined' && '' !== cspMainKeyword) {
    text = document.createElement('div');
    text.textContent = cspMainKeyword?? '-';
    text.className = 'csp-text csp-main-keyword';
    tBody.append(text);
  }

  // add secondary keywords
  if (typeof cspSecondaryKeywords !== 'undefined' && '' !== cspSecondaryKeywords) {
    text = document.createElement('div');
    text.textContent = cspSecondaryKeywords?? '-';
    text.className = 'csp-text csp-secondary-keywords';
    tBody.append(text);
  }

  // add index now button if cspIndexNowEngines exists
  if (typeof cspIndexNowEngines !== 'undefined') {
    button = document.createElement('div');
    button.textContent = '[IN]';
    button.setAttribute('title', cspMessages[cspLang]['buttonIndexNow']);
    button.className = 'csp-button';

    // event
    button.addEventListener( 'click', function (event) {
      indexNow();
    });

    tHeader.append(button);
  }

  // add close button
  button = document.createElement('div');
  button.textContent = '✕';
  button.setAttribute('title', cspMessages[cspLang]['buttonHide']);
  button.className = 'csp-button csp-hide-toolbar';

  // event
  button.addEventListener( 'click', function (event) {
    // Collapse toolbar
    const toolbar = document.getElementById('cspToolbar');
    if (body.classList.contains('pdir-seoToolbar--hidden')) {
      localStorage.setItem('pdir/seoToolbar/displayState', 'visible');
      body.classList.add('pdir-seoToolbar--visible');
      body.classList.remove('pdir-seoToolbar--hidden');
      document.getElementsByClassName('csp-hide-toolbar')[0].innerHTML = '✕';
    } else {
      localStorage.setItem('pdir/seoToolbar/displayState', 'hidden');
      body.classList.add('pdir-seoToolbar--hidden');
      body.classList.remove('pdir-seoToolbar--visible');
      document.getElementsByClassName('csp-hide-toolbar')[0].innerHTML = '＋';
    }

    toggleElement('cspToolbarBody');
    toggleElement('cspToolbarFooter');
    toggleClass('cspToolbar', 'closed');
  });

  tHeader.append(button);

  // add headline
  text = document.createElement('div');
  text.className = 'csp-text csp-headline';
  text.textContent = cspMessages[cspLang]['seoAuditHeadline'];
  tBody.append(text);

  //////////////////////////////
  //// add features to body ////
  //////////////////////////////
  // check for existing main keyword
  if ('' === cspMainKeyword) {
    text = document.createElement('div');
    text.className = 'csp-check csp-keyword-title csp-icon csp-fail';
    text.textContent = cspMessages[cspLang]['mainKeywordNotExistsCheck'];
    tBody.append(text);
  }

  // main keyword title check
  text = document.createElement('div');
  text.className = 'csp-check csp-keyword-title csp-icon';
  if ('' !== cspMainKeyword && contains(document.title, cspMainKeyword)) {
    text.className += ' csp-success';
    text.textContent = cspMessages[cspLang]['mainKeywordTitleCheckSuccess']; // 'Perfekt, das Hauptkeyword ist im Titel enthalten.';
  } else {
    text.className += ' csp-fail';
    text.textContent = cspMessages[cspLang]['mainKeywordTitleCheckFail']; // 'Füge das Hauptkeyword zum Meta Titel hinzu.';
    totalFails++;
  }

  tBody.append(text); totalTests++;


  // main keyword description check
  text = document.createElement('div');
  text.className = 'csp-check csp-keyword-description csp-icon';
  if ('' !== cspMainKeyword && contains(getMeta('description'), cspMainKeyword)) {
    text.className += ' csp-success';
    text.textContent = cspMessages[cspLang]['mainKeywordMetaDescriptionCheckSuccess'];
  } else {
    text.className += ' csp-fail';
    text.textContent = cspMessages[cspLang]['mainKeywordMetaDescriptionCheckFail'];
    totalFails++;
  }
  tBody.append(text); totalTests++;

  // main keyword alias check
  text = document.createElement('div');
  text.className = 'csp-check csp-keyword-description csp-icon';
  if ('' !== cspMainKeyword && !cspIsRoot && aliasContains(location.pathname, cspMainKeyword)) {
    text.className += ' csp-success';
    text.textContent = cspMessages[cspLang]['mainKeywordAliasCheckSuccess'];
  } else if (cspIsRoot) {
    text.className += ' csp-neutral';
    text.textContent = cspMessages[cspLang]['mainKeywordAliasCheckNeutral'];
  } else {
    text.className += ' csp-fail';
    text.textContent = cspMessages[cspLang]['mainKeywordAliasCheckFail'];
    totalFails++;
  }

  tBody.append(text); totalTests++;

  // main keyword in first content check
  const main = document.getElementById('main');
  const cspText = main.innerText || main.textContent;
  let pNodes = getChildNodesOfElement('main', 'p', false);
  let pNodesText = getChildNodesOfElement('main', 'p');

  text = document.createElement('div');
  text.className = 'csp-check csp-keyword-description csp-icon';
  if (findStringInBeginningOfText(cspMainKeyword, pNodesText)) {
    text.className += ' csp-success';
    text.textContent = 'Sehr gut, das Hauptkeyword ist am Anfang des ersten Inhalts zu finden.';
  } else {
    text.className += ' csp-fail';
    text.textContent = 'Nutze das Hauptkeyword am Anfang des ersten Inhalts.';
    totalFails++;
  }

  tBody.append(text); totalTests++;

  // content length check
  let wordCount = countWords(cspText);

  text = document.createElement('div');
  text.className = 'csp-check csp-content-length csp-icon';
  if (wordCount < 800) {
    text.className += ' csp-fail';
    text.textContent = `Der Inhalt enthält ${wordCount.toLocaleString(cspLang)} Wörter. Versuche mindestens 800 Wörter zu verwenden.`;
    totalFails++;
  } else {
    text.className += ' csp-success';
    text.textContent = `Der Inhalt enthält ${wordCount.toLocaleString(cspLang)} Wörter. Sehr gut!`;
  }

  tBody.append(text); totalTests++;

  // robots meta check
  text = document.createElement('div');
  text.className = 'csp-check csp-robots csp-icon';
  text.textContent = getMeta('robots');
  if ('index,follow' === getMeta('robots')) {
    text.className += ' csp-success';
  } else if('index,nofollow' === getMeta('robots')) {
    text.className += ' csp-warning';
  } else {
    text.className += ' csp-fail';
    totalFails++;
  }

  tBody.append(text); totalTests++;

  // section 2
  panel = document.createElement('div');
  panel.setAttribute('id', 'cspPanel1');
  panel.className = 'csp-panel csp-text csp-headline';
  panel.textContent = cspMessages[cspLang]['seoAuditReadabilityHeadline'];

  // event
  panel.addEventListener( 'click', function (event) {
    toggleElement('cspSection1');
    toggleClass('cspPanel1', 'open');
  });

  tBody.append(panel);

  container = document.createElement('div');
  container.setAttribute('id', 'cspSection1');
  container.className = 'csp-container csp-hide';
  container.style.display = 'none';

  // image alt check
  let images = getChildNodesOfElement('main', 'img', false);
  // @todo add mainKeywordInImageAlt check!!!
  let mainKeywordInImageAlt = false;


  text = document.createElement('div');
  text.className = 'csp-check csp-content-length csp-icon';
  if ('' !== cspMainKeyword && images.length > 3 && mainKeywordInImageAlt) {
    text.className += ' csp-success';
    text.textContent = cspMessages[cspLang]['imageAltCheckSuccess'];
  } else if ('' !== cspMainKeyword && images.length > 3) {
    text.className += ' csp-neutral';
    text.textContent = cspMessages[cspLang]['imageAltCheckNeutral'];
  } else {
    text.className += ' csp-fail';
    text.textContent = cspMessages[cspLang]['imageAltCheckFail'];
    totalFails++;
  }
  container.append(text); totalTests++;

  // internal link check
  let internalLinks = getChildNodesOfElement('main', 'a', false);

  text = document.createElement('div');
  text.className = 'csp-check csp-internal-links csp-icon';
  if ('' !== cspMainKeyword && internalLinks.length > 3) {
    text.className += ' csp-success';
    text.textContent = cspMessages[cspLang]['internalLinkCheckSuccess'];
  } else {
    text.className += ' csp-fail';
    text.textContent = cspMessages[cspLang]['internalLinkCheckFail'];
    totalFails++;
  }
  container.append(text); totalTests++;
  tBody.append(container);

  // section 3
  // word frequency
  // https://stackoverflow.com/a/30335883

  toolbar.append(tHeader);
  toolbar.append(tBody);

  // fixed toolbar
  container = document.createElement('div');
  container.setAttribute('id', 'cspToolbarFooter');
  container.className = 'csp-toolbar-footer';

  // add layout overlapped button
  button = document.createElement('div');
  button.textContent = '🗗';
  button.setAttribute('title', cspMessages[cspLang]['buttonOverlap']);
  button.className = 'csp-button csp-overlap';

  // event
  button.addEventListener( 'click', function (event) {
    // change toolbar layout
    if (body.classList.contains('pdir-seoToolbar--normal')) {
      localStorage.setItem('pdir/seoToolbar/layoutState', 'overlapped');
      body.classList.add('pdir-seoToolbar--overlapped');
      body.classList.remove('pdir-seoToolbar--normal');
    } else {
      localStorage.setItem('pdir/seoToolbar/layoutState', 'normal');
      body.classList.add('pdir-seoToolbar--normal');
      body.classList.remove('pdir-seoToolbar--overlapped');
    }
  });

  container.append(button);
  toolbar.append(container);
  // \end fixed toolbar

  body.append(toolbar);

  // update total
  let total = document.getElementById('cspTotal');
  let points = totalTests-totalFails;
  let percent = (points / totalTests) * 100;

  total.innerText = points+' / '+totalTests;

  if (percent < totalNeutralPercent) {
    total.classList.add('neutral');
  } else if (percent < totalFailPercent) {
    total.classList.add('fail');
  }

  // set display state
  if ('hidden' === localStorage.getItem('pdir/seoToolbar/displayState')) {
    toolbar.classList.add('closed');
    toggleElement('cspToolbarBody');
  }
}

function indexNow () {
  cspIndexNowEngines.forEach((engine) => {
    sendUrlToSearchEngine(engine);
  });

/*
  var uri = 'https://<searchengine>/indexnow?url=http://www.example.com/product.html&key=8af1fafdf59041c3bd96c642dc3a4243&keyLocation=http://www.example.com/myIndexNowKey63638.txt
  fetch('http://localhost:4200/dashboard', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      host: '',
      key: '8af1fafdf59041c3bd96c642dc3a4243',

    })
  });
  */
}

async function sendUrlToSearchEngine (engine) {
  let url = 'https://'+cspSearchEngines[engine]+'/indexnow?url='+cspCurrentUrl+'&key=indexNow'+cspIndexNowKey;
  const response = await fetch(url);
  const res = await response;
  // @todo implement error handling
}

// get meta data
function getMeta(metaName) {
  const metas = document.getElementsByTagName('meta');

  for (let i = 0; i < metas.length; i++) {
    if (metas[i].getAttribute('name') === metaName) {
      return metas[i].getAttribute('content');
    }
  }

  return '';
}

function contains(str, searchStr) {
  if(str.includes(searchStr) || str.includes(searchStr.toLowerCase()) || str.toLowerCase().includes(searchStr.toLowerCase()))
    return true;

  return false;
}

function aliasContains(str, searchStr) {
  if(str.includes(searchStr) ||
    str.includes(searchStr.toLowerCase()) ||
    str.includes(searchStr.replaceAll(' ', '-').toLowerCase())  ||
    str.includes(searchStr.replaceAll(' ', '_').toLowerCase())
  )
    return true;

  return false;
}

function countWords(str) {
  let matches = str.match(/[\w\d\’\'-]+/gi);
  return matches ? matches.length : 0;
}

function truncate(str, words) {
  return str.split(' ').splice(0, words).join(' ');
}

function findStringInBeginningOfText(str, text) {
  // we want to find the string in the first 10% of the text
  let res = (10 / 100) * countWords(text);
  text = truncate(text, res);

  if (contains(text, str))
    return true;

  return false;
}

function toggleElement(id) {
  let x = document.getElementById(id);
  if (x.style.display === 'none') {
    x.style.display = 'block';
  } else {
    x.style.display = 'none';
  }
}

function toggleClass(id, className) {
  let element = document.getElementById(id);
  element.classList.toggle(className);
}

function getChildNodesOfElement(id, type, asText = true) {
  let res = [],
  text = '';
  // let nodes = document.getElementById(id).childNodes;
  let nodes = document.getElementById(id).getElementsByTagName(type);

  for(let i=0; i<nodes.length; i++) {
    if (nodes[i].nodeName.toLowerCase() === type) {
      res.push(nodes[i]);
      text += nodes[i].innerText || nodes[i].textContent;
    }
  }

  if (asText) {
    return text;
  }

  return nodes;
}
