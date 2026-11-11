export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,POST');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') return res.status(200).end();

  const apiKey = 'gw_2d95805008dfdc532b71f80f5cf2c66a20e9de366d5f61e9f3c2a70afc138194';
  const targetUrl = 'https://api.silkpay.live/merchant/api/payin/create';

  try {
    const response = await fetch(targetUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${apiKey}`,
      },
      body: typeof req.body === 'string' ? req.body : JSON.stringify(req.body || {}),
    });
    const data = await response.json();
    return res.status(response.status || 200).json(data);
  } catch (err) {
    return res.status(500).json({ code: -1, msg: err.message });
  }
}
