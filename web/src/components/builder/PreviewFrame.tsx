type PreviewFrameProps = {
  pageTitle: string;
};

export function PreviewFrame({ pageTitle }: PreviewFrameProps) {
  return (
    <section className="section-card">
      <h2>Preview</h2>
      <div className="preview-frame">
        <div className="preview-topbar">Previewing {pageTitle}</div>
        <div className="preview-canvas">
          <p>Your live tenant page preview renders here.</p>
        </div>
      </div>
    </section>
  );
}