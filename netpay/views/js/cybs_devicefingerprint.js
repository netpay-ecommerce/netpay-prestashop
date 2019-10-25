function cybs_dfprofiler(organizationId, sessionId, merchantID) {
    var img = document.createElement("img"); // one-pixel Image code
    var objectTm = document.createElement("object"); // flash code
    var paragraphTm = document.createElement("p");
    var param = document.createElement("param");
    var str = "";
    var tmpScript = document.createElement("script"); // js file
  
    str = "background:url(https://h.online-metrix.net/fp/clear.png?org_id=" + organizationId + "&session_id=" + merchantID + sessionId + "&m=1)";
    paragraphTm.styleSheets = str;
  
    document.body.appendChild(paragraphTm);
  
    str = "https://h.online-metrix.net/fp/clear.png?org_id=" + organizationId + "&session_id=" + merchantID + sessionId + "&m=2";
    img.src = str;
    img.alt = "";
  
    document.body.appendChild(img);
  
    objectTm.data = "https://h.online-metrix.net/fp/fp.swf?org_id=" + organizationId + "&session_id=" + merchantID + sessionId;
    objectTm.type = "application/x-shockwave-flash";
    objectTm.width = "1";
    objectTm.height = "1";
    objectTm.id = "thm_fp";
  
    param.name = "movie";
    param.value = "https://h.online-metrix.net/fp/fp.swf?org_id=" + organizationId + "&session_id=" + merchantID + sessionId;
  
    objectTm.appendChild(param);
    document.body.appendChild(objectTm);
  
    tmpScript.src = "https://h.online-metrix.net/fp/tags.js?org_id=" + organizationId + "&session_id=" + merchantID + sessionId;
    tmpScript.type = "text/javascript";
    document.body.appendChild(tmpScript);
  
    return sessionId;
  }
  