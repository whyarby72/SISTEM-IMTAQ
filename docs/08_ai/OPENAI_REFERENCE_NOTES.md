# OpenAI Reference Notes

These are implementation references, not project governance authority. **Verify them again at AI implementation time because API capabilities, data controls, models and pricing can change.**

Current official references reviewed when workspace v1.2 was prepared:

- Developer quickstart / Responses API / tools: https://platform.openai.com/docs/quickstart/make-your-first-api-request
- Responses/tool definitions and function calling reference: https://platform.openai.com/docs/api-reference/responses
- API usage/cost review: https://help.openai.com/en/articles/10478918-reviewing-api-usage-and-costs
- Token concepts/counting: https://help.openai.com/en/articles/4936856-understanding-and-counting-tokens
- Account/API-key security guidance: https://help.openai.com/en/articles/8304786
- OpenAI platform data controls: https://platform.openai.com/docs/models/default-usage-policies-by-endpoint

Workspace design implications:
- keep `OPENAI_API_KEY` server-side;
- use tool/function schemas rather than arbitrary SQL;
- capture provider usage metadata for token observability;
- verify provider retention/data-control configuration before production enablement;
- keep provider/model/pricing details configurable and revalidated before rollout.
