export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);
    
    // 1. Verify Cloudflare Access JWT Assertion
    const jwt = request.headers.get("Cf-Access-Jwt-Assertion");
    if (!jwt) {
      return new Response("Missing Cf-Access-Jwt-Assertion header. Please access through Cloudflare Access.", { status: 401 });
    }

    try {
      const isValid = await verifyToken(jwt, env.JWKS_URL, env.AUDIENCE);
      if (!isValid) {
        return new Response("Invalid Cloudflare Access Token.", { status: 403 });
      }
    } catch (err) {
      return new Response(`Error validating token: ${err.message}`, { status: 500 });
    }

    // 2. Proxy request to the active Cloudflare Tunnel URL
    if (!env.TUNNEL_URL) {
      return new Response("Backend tunnel URL is not configured in Worker variables.", { status: 503 });
    }

    const targetUrl = new URL(env.TUNNEL_URL + url.pathname + url.search);
    const proxyHeaders = new Headers(request.headers);

    const proxyRequest = new Request(targetUrl, {
      method: request.method,
      headers: proxyHeaders,
      body: request.body,
      redirect: "manual"
    });

    try {
      const response = await fetch(proxyRequest);
      return response;
    } catch (err) {
      return new Response(`Failed to contact backend tunnel: ${err.message}`, { status: 502 });
    }
  }
};

// Verify JWT using Web Crypto API inside Workers
async function verifyToken(jwt, jwksUrl, audience) {
  const parts = jwt.split('.');
  if (parts.length !== 3) return false;

  const [headerB64, payloadB64, signatureB64] = parts;
  
  // Verify payload
  const payload = JSON.parse(atob(payloadB64.replace(/-/g, '+').replace(/_/g, '/')));
  const now = Math.floor(Date.now() / 1000);
  if (payload.exp && payload.exp < now) {
    console.error("Token has expired");
    return false;
  }
  if (payload.aud !== audience) {
    console.error("Audience mismatch: expected", audience, "got", payload.aud);
    return false;
  }

  // Fetch JWKs public keys
  const header = JSON.parse(atob(headerB64.replace(/-/g, '+').replace(/_/g, '/')));
  const kid = header.kid;
  if (!kid) return false;

  const response = await fetch(jwksUrl);
  const jwks = await response.json();
  const jwk = jwks.keys.find(k => k.kid === kid);
  if (!jwk) {
    console.error("JWK not found for kid", kid);
    return false;
  }

  // Import JWK key
  const key = await crypto.subtle.importKey(
    "jwk",
    jwk,
    {
      name: "RSASSA-PKCS1-v1_5",
      hash: { name: "SHA-256" }
    },
    false,
    ["verify"]
  );

  // Validate signature
  const data = new TextEncoder().encode(`${headerB64}.${payloadB64}`);
  const signature = new Uint8Array(
    atob(signatureB64.replace(/-/g, '+').replace(/_/g, '/'))
      .split("")
      .map(c => c.charCodeAt(0))
  );

  return await crypto.subtle.verify(
    "RSASSA-PKCS1-v1_5",
    key,
    signature,
    data
  );
}
