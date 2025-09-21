FROM node:22-bookworm-slim
WORKDIR /app

# copy only manifest files first (npm cache)
COPY package*.json ./
RUN npm install

# copy the rest of the project
COPY . .

EXPOSE 3000

# dev server
CMD ["npm","run","dev"]
