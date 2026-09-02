---
tags:
  - domain
  - frontend
  - integrations
---

# Prediction markets

The public `markets.php` page mirrors the Markets view from the supplied Project Orion
prototype while using the production site's shared header, themes, responsive layout, and
RU/UK localization.

## Data source

The page is read-only and loads public data directly from the official Manifold API:

- `GET /v0/user/toffexcrf` for the fixed profile;
- `GET /v0/markets?userId=...` for every market created by that account;
- `GET /v0/bets?username=toffexcrf` for every public bet by that account;
- `GET /v0/market/{id}` to resolve the question for markets referenced by bets and to load
  the complete answer/option model for the selected market.

Both list endpoints request 1,000 records per page and follow the `before` cursor until the
API returns the final page. IDs are deduplicated so a repeated cursor cannot duplicate
records or create an endless loop.

## Rendering

`js/markets.js` renders API strings with DOM `textContent`, keeps external links restricted
to `https://manifold.markets/`, and never sends or stores a Manifold API key. The selected
market panel includes a canvas probability chart; the history below initially renders all
bets and offers optional YES/NO filters.

The voting panel is selected from the API `outcomeType`:

- `BINARY` renders YES and NO actions with their current probabilities;
- `MULTIPLE_CHOICE` and `FREE_RESPONSE` render every answer, its probability bar, and
  answer-level YES/NO actions. `shouldAnswersSumToOne` labels the market as single-winner
  or independent;
- `POLL` renders each option with vote count and share;
- `NUMERIC` and `PSEUDO_NUMERIC` render the current value, range, and numeric forecast action;
- `BOUNTIED_QUESTION` renders the remaining bounty and add-answer action;
- unknown future types retain a generic safe link to the Manifold market.

All voting actions open the official Manifold page in a new tab. The Orion page remains
read-only and does not request, transmit, or store a Manifold API key.

The route is linked from the shared header and footer and is listed in `sitemap.xml`.

Related: [[Design system]], [[Localization]], [[Security]]
