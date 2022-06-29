export default function(p, o) {
  return p.reduce((xs, x) => (xs && xs[x] ? xs[x] : null), o);
}
