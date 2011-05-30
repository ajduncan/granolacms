function new_window(name, url, hsize, vsize) {
  var winl = (screen.width - hsize) / 2;
  var wint = (screen.height - vsize) / 2;
  link = window.open(url,name,"toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=" + hsize + ",height=" + vsize + ",left=" + winl + ",top=" + wint);
}