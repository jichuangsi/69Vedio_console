var i = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";

function u() {
	console.log(123)
  var e = document.createElement("canvas");
  if (e && "function" == typeof e.getContext)
    for (var t = ["webgl", "webgl2", "experimental-webgl2", "experimental-webgl"], r = 0; r < t.length; r++) {
      var i = t[r],
        o = e.getContext(i);
      if (o) {
        var a = {};
        a.context = i, a.version = o.getParameter(o.VERSION), a.vendor = o.getParameter(o.VENDOR), a.sl_version = o.getParameter(o.SHADING_LANGUAGE_VERSION), a.max_texture_size = o.getParameter(o.MAX_TEXTURE_SIZE);
        var c = o.getExtension("WEBGL_debug_renderer_info");
        return c && (a.vendor = o.getParameter(c.UNMASKED_VENDOR_WEBGL), a.renderer = o.getParameter(c.UNMASKED_RENDERER_WEBGL)), a
      }
    }
  return {}
}
function c (n) {
  if (!n) return "";
  let r = t(n), i = r.length, o = 0
  for (; o < i; o++) r[o] = 150 ^ r[o];
  return e(r, !0)
}
function t(e) {
  let n, t = -1,
    r = e.length,
    i = [];
  if (/^[\x00-\x7f]*$/.test(e))
    for (; ++t < r;) i.push(e.charCodeAt(t));
  else
    for (; ++t < r;) n = e.charCodeAt(t), n < 128 ? i.push(n) : n < 2048 ? i.push(n >> 6 | 192, 63 & n | 128) : i.push(n >> 12 | 224, n >> 6 & 63 | 128, 63 & n | 128);
  return i
}
function e(e, n) {
  var t, r, o, a, c = -1,
    u = e.length,
    l = [0, 0, 0, 0];
  for (t = []; ++c < u;) r = e[c], o = e[++c], l[0] = r >> 2, l[1] = (3 & r) << 4 | (o || 0) >> 4, c >= u ? l[2] = l[3] = 64 : (a = e[++c], l[2] = (15 & o) << 2 | (a || 0) >> 6, l[3] = c >= u ? 64 : 63 & a), t.push(i.charAt(l[0]), i.charAt(l[1]), i.charAt(l[2]), i.charAt(l[3]));
  let s = t.join("");
  return n ? s.replace(/=/g, "") : s
}

