const fs = require('fs');

fs.readdir(".", {}, function (err, filenames) {
  if (err) {
    throw new Error("failed to read directory");
  }
  console.log("found " + filenames.length + " files");

  filenames.filter(f => f !== "migrate.js" && f.endsWith(".js")).forEach(filename => {
    parseModuleExportToJsonObject(filename);
  })
});

function parseModuleExportToJsonObject(filename) {
  fs.readFile(filename, "utf8", function (err, content) {
    if (err) {
      throw new Error("failed to load file " + filename);
    }
    console.log("read " + filename);

    let newContent = removeModuleExport(content);
    newContent = correctJsonKeys(newContent);

    const newFilename = filename.replace(".js", ".json");
    fs.writeFile(newFilename, newContent, 'utf8', function (err) {
      if (err) {
        throw new Error("writing to file failed");
      }

      console.log("converted " + filename + " to json object");
    })
  });
}

function removeModuleExport(content) {
  return content.substring(content.indexOf("{"));
}

function correctJsonKeys(content) {
  let result = [];
  for (const line in content.split("\n")) {
    const parts = line.split(":");

    if (parts.length === 1) {
      result.push(parts[0]);
    } else {
      const key = '"' + parts[0].trim() + '"';

      let noQuotes = parts.splice(0, 1).join(":").trim();
      noQuotes = noQuotes.substring(1, noQuotes.length - 2);
      const value = '"' + noQuotes + '"';

      result.push(key + ":" + value);
    }
  }

  return result.join("\n");
}