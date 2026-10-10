function submitLead(e){
  e.preventDefault();
  var f=e.target, d=new FormData(f);
  var msg="Hi! I want to book a FREE body scan.\nName: "+d.get("name")+"\nArea: "+d.get("area")+
          "\nGoal: "+d.get("goal")+"\nPreferred time: "+d.get("time")+"\n(Source: "+f.dataset.source+")";
  if(window.gtag){gtag("event","generate_lead",{form_source:f.dataset.source,area:d.get("area"),goal:d.get("goal")});}
  // Optional: also send to a Google Sheet via Apps Script web app. Set SHEET_URL to enable.
  var SHEET_URL="";
  if(SHEET_URL){
    var body=new URLSearchParams();d.forEach(function(v,k){body.append(k,v)});body.append("source",f.dataset.source);
    fetch(SHEET_URL,{method:"POST",mode:"no-cors",body:body});
  }
  window.open("https://wa.me/"+WA_NUMBER+"?text="+encodeURIComponent(msg),"_blank");
  window.location.href="/thank-you/";
  return false;
}
function karadaReport(e){
  e.preventDefault();
  var d=Object.fromEntries(new FormData(e.target));
  var bmi="";
  if(d.weight&&d.height){var h=d.height/100;bmi=(d.weight/(h*h)).toFixed(1);}
  // BMI bands for Asian adults; visceral fat bands as used on Karada Scan monitors
  var bmiBand=!bmi?"":bmi<18.5?"Underweight":bmi<23?"Normal":bmi<25?"Overweight":"Obese";
  var v=parseFloat(d.visceral),vBand=isNaN(v)?"":v<10?"Normal":v<15?"High":"Very high";
  var ageGap=(d.bodyage&&d.age)?(d.bodyage-d.age):null;
  var lines=[
    "*Natural Wellness Club - Karada Scan Report*",
    "Name: "+d.name+" | Age: "+d.age+" | "+d.gender,
    "Scan: "+d.scantype+" | Date: "+new Date().toLocaleDateString("en-IN"),
    "",
    "Weight: "+(d.weight||"-")+" kg",
    "BMI: "+(bmi||"-")+(bmiBand?" ("+bmiBand+")":""),
    "Body fat: "+(d.bodyfat||"-")+" %",
    "Visceral fat level: "+(d.visceral||"-")+(vBand?" ("+vBand+")":""),
    "Skeletal muscle: "+(d.muscle||"-")+" %",
    "Subcutaneous fat: "+(d.subq||"-")+" %",
    "Resting metabolism: "+(d.rm||"-")+" kcal/day",
    "Body age: "+(d.bodyage||"-")+(ageGap!==null?" ("+(ageGap>0?ageGap+" yrs above":Math.abs(ageGap)+" yrs below")+" actual age)":""),
  ];
  if(d.notes)lines.push("","Coach notes: "+d.notes);
  lines.push("","General wellness information, not medical advice.");
  var text=lines.join("\n");
  var r=document.getElementById("report");
  r.hidden=false;
  r.innerHTML="<pre style='white-space:pre-wrap;font-family:inherit'>"+text.replace(/[<>&]/g,function(c){return{"<":"&lt;",">":"&gt;","&":"&amp;"}[c]})+"</pre>"+
    "<a class='btn' target='_blank' href='https://wa.me/"+d.phone.replace(/[^0-9]/g,"").replace(/^(\d{10})$/,"91$1")+"?text="+encodeURIComponent(text)+"'>Send to client on WhatsApp</a>"+
    "<button class='btn ghost' onclick='window.print()'>Print</button>";
  var SCAN_SHEET_URL=""; // optional Google Apps Script URL to log scans
  if(SCAN_SHEET_URL){var b=new URLSearchParams(d);b.append("bmi",bmi);fetch(SCAN_SHEET_URL,{method:"POST",mode:"no-cors",body:b});}
  r.scrollIntoView({behavior:"smooth"});
  return false;
}
