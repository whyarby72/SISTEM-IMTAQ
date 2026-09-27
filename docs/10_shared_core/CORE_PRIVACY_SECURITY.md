# Shared Core Privacy & Security v1.0

## 1. Data minimization
Every consumer receives only fields necessary for its purpose.

Example: an Academic teacher may need Student name/code/class context, but not automatically NISN, guardian WhatsApp, audit logs or sensitive Kesantrian/Kepengasuhan records.

## 2. Suggested classification
- Student display identity: `INTERNAL`.
- NIS/NISN and birth data: `RESTRICTED`.
- Guardian contact/address: `RESTRICTED`.
- Staff contact/private details: `RESTRICTED` as applicable.
- Authentication credentials/secrets: `HIGHLY_RESTRICTED` / platform secret, never business-exported.
- Audit logs: `RESTRICTED`, possibly higher for security incidents.

Final institutional classification policy can refine these labels.

## 3. Least privilege
- Core master mutation permissions are separate from domain transaction permissions.
- Student view does not imply identifier view.
- Guardian view does not imply contact export.
- `SUPER_ADMIN` has full institution-wide authority, but approval still follows the applicable business workflow and is never automatic.
- exports require explicit permissions and should be audited for restricted data.

## 4. Historical preservation vs privacy
Business history is preserved; this does not mean all historical contact/identity values are visible to all users. Historical sensitive values can remain restricted to authorized administration/audit contexts.

## 5. Parent Portal
Future Parent Portal identity must resolve an authenticated Guardian to explicit authorized Student relationships. It must never rely on a guessed Student URL or phone number alone.

## 6. AI
AI tools receive minimum necessary Core fields. NISN, guardian contacts and restricted audit fields are not included unless the tool purpose and user authorization explicitly require them.

## 7. Communication
WhatsApp/communication layer obtains only eligible recipient context for an approved purpose. Contact verification and consent are evaluated separately.

## 8. Logs/secrets
Do not log passwords, session tokens, OpenAI keys, WhatsApp provider secrets or raw secret headers. Redact restricted request payloads where needed.
