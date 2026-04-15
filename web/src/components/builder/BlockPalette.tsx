const blocks = [
  { name: 'Hero', description: 'Headline, subheadline, and CTA.' },
  { name: 'Services', description: 'Service cards and pricing snippets.' },
  { name: 'Doctors', description: 'Team profiles with bios.' },
  { name: 'Testimonials', description: 'Social proof and ratings.' },
  { name: 'FAQ', description: 'Common questions with accordions.' }
];

export function BlockPalette() {
  return (
    <section className="section-card">
      <h2>Block palette</h2>
      <ul className="builder-list">
        {blocks.map((block) => (
          <li key={block.name}>
            <strong>{block.name}</strong>
            <p>{block.description}</p>
          </li>
        ))}
      </ul>
    </section>
  );
}