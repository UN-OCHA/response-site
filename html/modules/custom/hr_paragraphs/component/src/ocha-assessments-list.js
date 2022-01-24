import { html, css } from 'lit-element';
import {unsafeHTML} from 'lit-html/directives/unsafe-html.js';
import { OchaAssessmentsBase } from './ocha-assessments-base.js';
import { paginationStyles } from './ocha-assessments-styles.js';
import { tableStyles } from './ocha-assessments-styles.js';

// Extend the LitElement base class
class OchaAssessmentsList extends OchaAssessmentsBase {
  static get styles() {
    return [
      super.styles,
      paginationStyles,
      tableStyles,
      css`
        :host {
          display: block;
          border: 1px solid transparent;
        }`
    ]
  }

  buildDocument(prefix, data, title) {
    switch (data[prefix + '_accessibility']) {
      case 'Publicly Available':
        if (data[prefix + '_file_url']) {
          return html`
            <div class="assessment-document">
              <div class="assessment-document-title">${title}</div>
              <a class="assessment-document-link" href="${this.baseurl}/${data[prefix + '_file_url']}">${data[prefix + '_description']}</a>
            </div>
          `;
        }
        break;

      case 'Available on Request':
        return html`
          <div class="assessment-document">
            <div class="assessment-document-title">${title}</div>
            <p>Available on Request.</p>
            <p>${data[prefix + '_instructions']}</p>
          </div>
        `;

    }
  }

  render() {
    if (!this.data) {
      return html`
        <div>Loading...</div>
        ${this.renderErrorMessage()}
      `;
    }

    return html`
      ${this.renderErrorMessage()}

      ${this.renderDropdowns()}

      <ul class="cd-list">
        ${
          this.data.map(
            r =>
              html`
                <li>
                  <h2 class="cd-list__title"><a href="${this.baseurl}/assessment/${r.uuid}">${r.title}</a></h2>
                  <div class="cd-list__description">
                    <p>
                      <span class="label">Leading/Coordinating Organization(s): </span>
                      <span class="values">${unsafeHTML(r.field_organizations_label)}</span>
                    </p>
                    <p>
                      <span class="label">Status: </span>
                      <span class="values">${r.field_status}</span>
                    </p>
                    <p>
                      <span class="label">Assessment Date(s): </span>
                      <span class="values">${this.renderDate(r)}</span>
                    </p>
                  </div>
                </li>
                `
        )}
      </ul>

      ${this.renderPager()}
    `;
  }

  connectedCallback() {
    super.connectedCallback();
  }

}

customElements.define('ocha-assessments-list', OchaAssessmentsList);

