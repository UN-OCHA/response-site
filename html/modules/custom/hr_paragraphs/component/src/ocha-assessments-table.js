import { html, css } from 'lit-element';
import { OchaAssessmentsBase } from './ocha-assessments-base.js';
import { paginationStyles } from './ocha-assessments-styles.js';
import { tableStyles } from './ocha-assessments-styles.js';

// Extend the LitElement base class
class OchaAssessmentsTable extends OchaAssessmentsBase {
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

      <table class="cd-table cd-table--striped">
        <thead>
          <tr>
            <th>Title</th>
            <th>Location(s)</th>
            <th>Leading/Coordinating Organization</th>
            <th>Participating Organization(s)</th>
            <th>Clusters/Sectors</th>
            <th>Status</th>
            <th>Assessment Date(s)</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          ${
            this.data.map(
              r =>
                html`
                  <tr>
                    <td data-content="Title"><a href="${this.baseurl}/assessment/${r.uuid}">${r.title}</a></td>
                    <td data-content="Location(s)">${r.field_locations_label.join(', ')}</td>
                    <td data-content="Managed by">${r.field_organizations_label}</td>
                    <td data-content="Participating Organization(s)">${r.field_asst_organizations_label}</td>
                    <td data-content="Clusters/Sectors">${r.field_local_groups_label}</td>
                    <td data-content="Status">${r.field_status}</td>
                    <td data-content="Assessment Date(s)">${this.renderDate(r)}</td>
                    <td data-content="Data">${this.buildDocument('report', r, 'Report')}${this.buildDocument('questionnaire', r, 'Questionnaire')}${this.buildDocument('data', r, 'Data')}</td>
                  </tr>
                  `
          )}
        </tbody>
      </table>

      ${this.renderPager()}
    `;
  }

  connectedCallback() {
    super.connectedCallback();
  }

}

customElements.define('ocha-assessments-table', OchaAssessmentsTable);

